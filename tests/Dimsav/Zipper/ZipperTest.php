<?php

use Dimsav\Zipper;

class ZipperTest extends PHPUnit_Framework_TestCase {

    private $destinationFile = '';
    private $zipper;

    public function setUp()
    {
        $this->zipper = new Zipper();
        $this->destinationFile = __DIR__.'/../../temp/test.zip';
        if (file_exists($this->destinationFile)) unlink($this->destinationFile);
    }

    public function testSettingArguments()
    {
        $dir    = __DIR__.'/../../samples';
        $this->zipper->add($dir);
        $this->assertEquals($this->zipper->getFiles(), array(realpath($dir)));

        $exclude = __DIR__.'/../../samples/files/logs';
        $this->zipper->addExclude($exclude);
        $this->assertEquals($this->zipper->getExcludes(), array(realpath($exclude)));
    }

    public function testCreatesZipFile()
    {
        $this->zipper->add(__DIR__.'/../../samples');
        $this->zipper->setDestination($this->destinationFile);
        $this->zipper->compress();
        $this->assertFileExists($this->destinationFile);
    }

    public function testZipMultipleSources()
    {
        $this->zipper->add(__DIR__.'/../../samples');
        $this->zipper->add(__DIR__.'/../../Dimsav');
        $this->zipper->setDestination($this->destinationFile);
        $this->zipper->compress();
        $this->assertFileExists($this->destinationFile);
    }

    public function testExcludeFile()
    {
        $this->zipper->add(__DIR__.'/../../samples');

        $this->zipper->addExclude(__DIR__.'/../../samples/files/logs/log.txt');
        $this->zipper->setDestination(__DIR__.'/../../temp/test.zip');

        $this->zipper->compress();
        $this->assertFileExists(__DIR__.'/../../temp/test.zip');
    }

    public function testZipHigherDirectory()
    {
        $this->zipper->add(__DIR__.'/../../../../unix-zipper');
        $this->zipper->setDestination($this->destinationFile);
        $this->zipper->compress();
        $this->assertFileExists($this->destinationFile);
    }

}