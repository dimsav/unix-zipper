<?php namespace Dimsav;
use Dimsav\Exception\RuntimeException;

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

    private $destinationDirectory;

    public function add($file)
    {
        $this->files[] = $file;
    }

    public function getFiles()
    {
        return $this->files;
    }

    public function addExclude($exclude)
    {
        $this->excludes[] = $exclude;
    }

    public function getExcludes()
    {
        return $this->excludes;
    }

    public function compressAs($destinationFile)
    {
        $this->determineDestination($destinationFile);

        $excludesOption = $this->getExcludeCommandOption();
//var_dump($this->destinationDirectory); die;
        $this->chdir($this->destinationDirectory);
        var_dump("zip $excludesOption -r "); die;
        exec("zip $excludesOption -r ");

    }

    private function determineDestination($destinationFile)
    {
        $this->destinationFile = $destinationFile;
        $this->destinationDirectory = dirname($destinationFile);
    }

    private function getExcludeCommandOption()
    {
        if ( ! $this->excludes) return '';

        $output = ''; // -x folder/\* -x file.zip

        foreach ($this->excludes as $exclude)
        {
            $output .= ' '.$this->getExcludeCommandOptionSegment($exclude).' ';
        }

        return $output;
    }

    private function getExcludeCommandOptionSegment($exclude)
    {
        $segment = '';

        if ($this->isValidPath($exclude) && $this->isPathInsideFilesArray($exclude) )
        {
            $segment = $this->getExcludeForZipOption($exclude);

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

    private function getExcludeForZipOption($path)
    {
        $pathDirectory = "$this->destinationDirectory/";

        return substr($path, strlen($pathDirectory));
    }

    private function makeExcludeSegmentRecursive($excludePath)
    {
        return $this->isLastCharacterASlash($excludePath) ? '\*' : '/\*';
    }

    private function isLastCharacterASlash($string)
    {
        return substr($string, -1, 1) == '/';
    }

    private function chdir($directory)
    {
        if ( ! $this->isValidPath($directory))
        {
            mkdir($directory, 0777, true);
        }
        chdir($directory);
    }


}
