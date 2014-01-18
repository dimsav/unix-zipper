<?php namespace Dimsav;

class PathHelper {

    private $base;
    private $root;
    private $originalCwd;

    public function __construct()
    {
        $this->originalCwd = getcwd();
    }

    public function setBase($basePath, $rootPath = null)
    {
        $this->base = $basePath;
        $this->root = $rootPath;
    }

    public function chdirToBase()
    {
        if ( ! $this->base) return;

        if ($this->root)
        {
            $this->validatePath($this->root);
            chdir($this->root);
        }

        $this->validatePath($this->base);
        chdir($this->base);
    }

    public function chdirBack()
    {
        if ( ! $this->base) return;

        chdir($this->originalCwd);
    }

    public function isValid($path)
    {
        return is_dir($path) || is_file($path);
    }

    public function validatePath($path)
    {
        if ( ! $this->isValid($path)) throw new \InvalidArgumentException($path);
    }

    public function absoluteToRelativePath($fromPath, $toPath)
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

        return implode('/', $relativeParts);
    }

}