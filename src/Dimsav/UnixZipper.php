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

    public function add($file)
    {
        $this->validatePath($file);
        $this->files[] = realpath($file);
    }

    public function getFiles()
    {
        return $this->files;
    }

    public function exclude($exclude)
    {
        $this->validatePath($exclude);
        $this->excludes[] = realpath($exclude);
    }

    public function getExcludes()
    {
        return $this->excludes;
    }

    public function setDestination($destinationFile)
    {
        if ( ! $this->isValidPath(dirname($destinationFile)))
        {
            mkdir(dirname($destinationFile), 0777, true);
        }
        $this->validatePath(dirname($destinationFile));

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

    private function getPasswordOption()
    {
        return $this->password != '' ? "-P " .escapeshellarg($this->password) : '';
    }

    private function getAddedFilesOption()
    {
        $output = '';
        foreach ($this->files as $file)
        {
            $output .= ' '.$this->absoluteToRelativePath($this->destinationDirectory, $file).' ';
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

        if ($this->isValidPath($exclude) && $this->isPathInsideFilesArray($exclude) )
        {
            $segment = $this->absoluteToRelativePath($this->destinationDirectory, $exclude);

            if (is_dir($exclude))
            {
                $segment .= $this->makeExcludeSegmentRecursive($segment);
            }

            $segment = "-x $segment";
        }

        return $segment;
    }

    private function isValidPath($path)
    {
        return is_dir($path) || is_file($path);
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

    private function absoluteToRelativePath($fromPath, $toPath)
    {
        $this->validatePath($fromPath);
        $this->validatePath($toPath);
        $fromPath = trim(realpath($fromPath),'\\/');
        $toPath = trim(realpath($toPath),'\\/');

        $fromPathArray = preg_split('%[\\/]%', $fromPath);
        $toPathArray = preg_split('%[\\/]%', $toPath);

        $commonPartsCount = 0;

        for ($i = 0; $i < max(sizeof($fromPathArray), sizeof($toPathArray)); ++$i) {
            if (isset($fromPathArray[$i]) && isset($toPathArray[$i]))
            {
                if ($fromPathArray[$i] == $toPathArray[$i])
                {
                    $commonPartsCount ++;
                }
                else
                {
                    break;
                }
            }
            else
            {
                break;
            }

        }

        $relativeParts = array();

        /* Replacing each part of the fromPath remaining after the common directories with ..
         * to go to the common root of the two paths
         */
        if (sizeof($fromPathArray) > $commonPartsCount) {
            $replacementCount  = sizeof($fromPathArray) - $commonPartsCount;
            $relativeParts     = array_fill(0, $replacementCount, '..');
        }

        /*
         * Each part of the "to path" that remains after the common parts is merely
         * appended to the relative path.
         */
        if (sizeof($toPathArray) > $commonPartsCount) {
            $remainingToPathParts  = array_slice($toPathArray, $commonPartsCount);
            $relativeParts           = array_merge($relativeParts, $remainingToPathParts);
        }

        return $relativeParts ? implode('/', $relativeParts) : './';
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
        if ( ! $this->isValidPath($directory))
        {
            mkdir($directory, 0777, true);
        }
        chdir($directory);
    }

    private function validatePath($path)
    {
        if ( ! $this->isValidPath($path)) throw new \InvalidArgumentException($path);
    }
}
