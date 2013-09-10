<?php namespace Dimsav;

use Dimsav\Exception\RuntimeException;


class OldUnixZipper {

    // Basic parameters
    private $pathToBeZipped; // can be both file or folder
    private $pathToBeZippedName;
    private $pathToBeZippedParentPath;

    private $destinationFile;
    private $zipFileDirectoryPath;
    private $zipFileName;

    private $password;
    private $timestampFormat = 'Y-m-d_H.i';
    private $excludes = array();

    private $command = '';

    function __construct()
    {

    }

    public function setPathToBeZipped($path)
    {
        $this->pathToBeZipped           = $path;
        $this->pathToBeZippedParentPath = dirname($this->pathToBeZipped);
        $this->pathToBeZippedName       = basename($this->pathToBeZipped);
    }

    public function setZipFileDirectoryPath($directory)
    {
        $this->zipFileDirectoryPath = $directory;
    }

    public function setPassword($password)
    {
        $this->password = $password;
    }

    public function setExcludes(array $excludes)
    {
        $this->excludes = $excludes;
    }

    public function setTimestampFormat($format)
    {
        $this->timestampFormat = $format;
    }


    public function compress()
    {
        if ( !$this->isInputValid())
        {
            return false;
        }

        $destinationFile    = $this->getDestinationFile();
        $passwordOption = $this->getPasswordOption();
        $excludesOption = $this->getExcludesOption();

        $this->cd($this->pathToBeZippedParentPath);

        // -r zips recursively
        $this->command.=  "zip $passwordOption $excludesOption -r $destinationFile $this->pathToBeZippedName";

        return exec($this->command);
    }

    public function getDestinationFile()
    {
        $this->determineDestinationFile();
        return $this->destinationFile;
    }

    public function getZipFileName()
    {
        return $this->zipFileName;
    }

    private function cd($path)
    {
        $this->command .= "cd $path; ";
    }

    private function getPasswordOption()
    {
        return $this->password != '' ? "-P $this->password " : '';
    }

    private function isInputValid()
    {
        $isInputValid = true;
        if (!is_dir($this->zipFileDirectoryPath))
        {
            throw new RuntimeException("$this->zipFileDirectoryPath is not a directory.");
            $isInputValid = false;
        }
        if (!$this->isValidPath($this->pathToBeZipped))
        {
            throw new RuntimeException("$this->pathToBeZipped is not a directory of file.");
            $isInputValid = false;
        }
        return $isInputValid;
    }

    private function determineDestinationFile()
    {
        $this->determineZipFileName();

        if($this->zipFileDirectoryPath != '' && substr($this->zipFileDirectoryPath, -1, 1) != DIRECTORY_SEPARATOR)
        {
            $this->zipFileDirectoryPath .= DIRECTORY_SEPARATOR;
        }

        // Add path to destination file
        $this->destinationFile = $this->zipFileDirectoryPath . $this->zipFileName;

    }

    private function determineZipFileName()
    {
        if ($this->zipFileName) return;
        $timestamp = date($this->timestampFormat);

        $this->zipFileName = "{$timestamp}_$this->pathToBeZippedName";
        $this->zipFileName .= $this->fileNameExtensionIsNot($this->zipFileName, 'zip') ? '.zip' : '';
    }

    private function fileNameExtensionIsNot($fileName, $extension)
    {
        return $this->getFileNameExtension($fileName) != $extension;
    }

    private function getExcludesOption()
    {
        if (!$this->excludes) return '';

        $excludesOption = ''; // -x folder/\* -x file.zip

        foreach ($this->excludes as $exclude)
        {
            if ($this->isValidPath($exclude) && $this->isPathInsidePathToBeZipped($exclude) )
            {
                $convertedExclude = $this->getExcludeForZipOption($exclude);

                if (is_dir($exclude))
                {
                    $convertedExclude .= $this->makeExcludePathRecursive($convertedExclude);
                }

                $excludesOption .= "-x $convertedExclude ";
            }
        }

        return $excludesOption;
    }

    private function getExcludeForZipOption($path)
    {
        $pathDirectory = "$this->pathToBeZippedParentPath/";

        return substr($path, strlen($pathDirectory));
    }

    private function isPathInsidePathToBeZipped($path)
    {
        return strpos($path, "$this->pathToBeZippedParentPath/") === 0;
    }

    private function makeExcludePathRecursive($excludePath)
    {
        return $this->isLastCharacterASlash($excludePath) ? '\*' : '/\*';
    }

    private function isLastCharacterASlash($string)
    {
        return substr($string, -1, 1) == '/';
    }


    private function isValidPath($path)
    {
        return is_dir($path) || is_file($path);
    }

    private function getFileNameExtension($filename = '')
    {
        $filename_parts = explode(".", $filename);
        return end($filename_parts);
    }
}
