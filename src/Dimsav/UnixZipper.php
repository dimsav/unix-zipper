<?php namespace Dimsav;

/**
 * Author: http://twitter.com/dimsav
 *
 * Compress a file or folder using the unix zip function. Can compress using a password or can exclude some files or folders.
 * Note: Won't work in windows.
 *
 */
class UnixZipper
{

    private $files = array();
    private $excludes = array();
    private $destinationFile;
    private $destinationFileName;
    private $password;

    private $destinationDirectory;

    /**
     * @var PathHelper
     */
    private $pathHelper;

    public function __construct()
    {
        $this->pathHelper = new PathHelper();
    }

    public function add($file)
    {
        $this->pathHelper->chdirToBase();
        $this->pathHelper->validatePath($file);
        $this->files[] = realpath($file);
        $this->pathHelper->chdirBack();
    }

    public function getFiles()
    {
        return $this->files;
    }

    public function exclude($exclude)
    {
        $this->pathHelper->chdirToBase();
        $this->pathHelper->validatePath($exclude);
        $this->excludes[] = realpath($exclude);
        $this->pathHelper->chdirBack();
    }

    public function getExcludes()
    {
        return $this->excludes;
    }

    public function setDestination($destinationFile)
    {
        if ( ! $this->pathHelper->isValid(dirname($destinationFile)))
        {
            mkdir(dirname($destinationFile), 0777, true);
        }
        $this->pathHelper->validatePath(dirname($destinationFile));

        $this->destinationDirectory = realpath(dirname($destinationFile));
        $this->destinationFileName = basename($destinationFile);
        $this->destinationFile = "$this->destinationDirectory/$this->destinationFileName";
    }

    public function compress()
    {
        $excludesOption = $this->getExcludeCommandOption();
        $passwordOption = $this->getPasswordOption();
        $addedFiles = $this->getAddedFilesOption();

        $this->forceCd($this->destinationDirectory);
        $command = "zip $passwordOption $excludesOption -r $this->destinationFileName $addedFiles";
        exec($command);

    }

    public function setPassword($password)
    {
        $this->password = $password;
    }

    public function setAbsolutePathAsBase($base)
    {
        $this->pathHelper->setBase($base);
    }

    public function setRelativePathAsBase($base, $root = null)
    {
        $this->pathHelper->setBase($base, $root);
    }

    private function getPasswordOption()
    {
        return $this->password != '' ? "-P " .escapeshellarg($this->password) : '';
    }

    private function getAddedFilesOption()
    {
        $output = '';
        foreach ($this->files as $file)
        {
            $output .= ' '.$this->pathHelper->absoluteToRelativePath($this->destinationDirectory, $file).' ';
        }
        return $output;
    }

    private function getExcludeCommandOption()
    {
        if ( ! $this->excludes) return '';

        $output = ''; // -x folder/\* -x file.zip

        foreach ($this->excludes as $exclude)
        {
            $output .= ' '.$this->getExcludeCommandOptionSegment($exclude).' ';
        }

        return trim($output);
    }

    private function getExcludeCommandOptionSegment($exclude)
    {
        $segment = '';

        if ($this->pathHelper->isValid($exclude) && $this->isPathInsideFilesArray($exclude) )
        {
            $segment = $this->pathHelper->absoluteToRelativePath($this->destinationDirectory, $exclude);

            if (is_dir($exclude))
            {
                $segment .= $this->makeExcludeSegmentRecursive($segment);
            }

            $segment = "-x $segment";
        }

        return $segment;
    }

    private function isPathInsideFilesArray($path)
    {
        foreach ($this->files as $file)
        {
            if (strpos($path, "$file/") === 0)
                return true;
        }
        return false;
    }

    private function makeExcludeSegmentRecursive($excludePath)
    {
        return $this->isLastCharacterASlash($excludePath) ? '\*' : '/\*';
    }

    private function isLastCharacterASlash($string)
    {
        return substr($string, -1, 1) == '/';
    }

    private function forceCd($directory)
    {
        if ( ! $this->pathHelper->isValid($directory))
        {
            mkdir($directory, 0777, true);
        }
        chdir($directory);
    }
}
