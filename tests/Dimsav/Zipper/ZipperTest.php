<?php

use Dimsav\UnixZipper;

class ZipperTest extends PHPUnit_Framework_TestCase {

    private $destinationFile;
    private $extractDir;
    /** @var  UnixZipper */
    private $zipper;

    public function setUp()
    {
        $this->zipper          = new UnixZipper();
        $this->extractDir      = __DIR__.'/../../temp';
        $this->destinationFile = __DIR__.'/../../temp/test.zip';
    }

    public function testSettingArguments()
    {
        $dir = __DIR__.'/../../samples';
        $this->zipper->add($dir);
        $this->assertEquals($this->zipper->getFiles(), array(realpath($dir)));

        $exclude = __DIR__.'/../../samples/files/logs';
        $this->zipper->exclude($exclude);
        $this->assertEquals($this->zipper->getExcludes(), array(realpath($exclude)));
    }

    public function testCreatesZipFile()
    {
        $this->zipper->add(__DIR__.'/../../samples');
        $this->zipper->setDestination($this->destinationFile);
        $this->zipper->compress();
        $this->assertFileExists($this->destinationFile);

        $this->extract();
        $this->assertFileExists($this->extractDir.'/samples/files/logs/log.txt');
        $this->assertFileExists($this->extractDir.'/samples/files/sample.png');
    }

    public function testZipMultipleSources()
    {
        $this->zipper->add(__DIR__.'/../../samples');
        $this->zipper->add(__DIR__.'/../../../src/Dimsav');
        $this->zipper->setDestination($this->destinationFile);
        $this->zipper->compress();
        $this->assertFileExists($this->destinationFile);

        $this->extract();
        $this->assertFileExists($this->extractDir.'/samples/files/logs/log.txt');
        $this->assertFileExists($this->extractDir.'/samples/files/sample.png');
        $this->assertFileExists($this->extractDir.'/src/Dimsav/UnixZipper.php');
    }

    public function testExcludeFile()
    {
        $this->zipper->add(__DIR__.'/../../samples');

        $this->zipper->exclude(__DIR__.'/../../samples/files/logs/log.txt');
        $this->zipper->setDestination(__DIR__.'/../../temp/test.zip');
        $this->zipper->compress();
        $this->assertFileExists(__DIR__.'/../../temp/test.zip');

        $this->extract();
        $this->assertFileExists($this->extractDir.'/samples/files/sample.png');
        $this->assertFileNotExists($this->extractDir.'/samples/files/logs/log.txt');
    }

    public function testZipParentDirectoryWithPassword()
    {
        $password = 'test;zip test\'';
        $this->zipper->add(__DIR__.'/../../samples');
        $this->zipper->setDestination($this->destinationFile);
        $this->zipper->setPassword($password);
        $this->zipper->compress();
        $this->assertFileExists($this->destinationFile);

        $password = escapeshellarg($password);
        exec("unzip -P $password $this->destinationFile");

        $this->assertFileExists($this->extractDir.'/samples/files/sample.png');
        $this->assertFileExists($this->extractDir.'/samples/files/logs/log.txt');
    }

    public function testZipHigherDirectory()
    {
        $this->zipper->add(__DIR__.'/../../../src');
        $this->zipper->setDestination($this->destinationFile);
        $this->zipper->compress();
        $this->assertFileExists($this->destinationFile);

        $this->extract();
        $this->assertFileExists($this->extractDir.'/src/Dimsav/UnixZipper.php');
    }

    public function testZipSameDirectory()
    {
        $this->zipper->add(__DIR__.'/../../../src');
        $this->zipper->setDestination($this->destinationFile);
        $this->zipper->compress();
        $this->assertFileExists($this->destinationFile);

        $this->setUp();

        $this->zipper->add(__DIR__.'/../../temp');
        $this->zipper->setDestination(__DIR__.'/../../temp/test2.zip');
        $this->zipper->compress();
        $this->assertFileExists(__DIR__.'/../../temp/test2.zip');
    }

    private function extract()
    {
        $zip = new ZipArchive;
        if ($zip->open($this->destinationFile) === true) {
            $zip->extractTo($this->extractDir);
            $zip->close();
        }
    }

    public function tearDown()
    {
        if (is_dir($this->extractDir)) exec('rm -rf '.realpath($this->extractDir));
    }

}