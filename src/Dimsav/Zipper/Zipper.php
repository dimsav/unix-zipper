<?php namespace Dimsav;

/**
 * Author: http://twitter.com/dimsav
 *
 * Compress a file or folder using the unix zip function. Can compress using a password or can exclude some files or folders.
 * Note: Won't work in windows.
 *
 */
class Zipper
{

    private $files = array();
    private $excludes = array();
    private $destinationFile;
    private $destinationFileName;

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

    public function addExclude($exclude)
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
        $addedFiles = $this->getAddedFilesOption();

        $this->forceCd($this->destinationDirectory);
        $command = "zip $excludesOption -r $this->destinationFileName $addedFiles";
        exec($command);

    }

    private function getAddedFilesOption()
    {
        $output = '';
        foreach ($this->files as $file)
        {
            $output .= ' '.$this->getRelatedToPath($this->destinationDirectory, $file).' ';
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
            $segment = $this->getRelatedToPath($this->destinationDirectory, $exclude);

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

    private function getRelatedToPath($base, $path)
    {
        $this->validatePath($base);
        $this->validatePath($path);
        $base = trim(realpath($base),'\\/');
        $path = trim(realpath($path),'\\/');

        $output = '';
        $baseArray = preg_split('%[\\/]%', $base);

        $pathArray = preg_split('%[\\/]%', $path);

        $commonDepth = -1;
        $outsideDepth = 0;

        foreach ($baseArray as $key => $node)
        {

            if ( ! isset($pathArray[$key]))
            {
                $output .= '../';
                $outsideDepth++;
            }
            elseif ($node != $pathArray[$key])
            {
                $output .= '../';
                $commonDepth = $key;
                break;
            }
            else
            {
                $commonDepth = $key+1;
            }
        }

        if ($commonDepth >= 0)
        {
            for ($i = $commonDepth; $i < count($pathArray); $i++)
            {
                $output.=$pathArray[$i].'/';
            }
        }

        return rtrim($output,'/');
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
