<?php

use Dimsav\Zipper;

class ZipperTest extends PHPUnit_Framework_TestCase {

    public function testSettingArguments()
    {
        $zipper = new Zipper();

        $dir    = __DIR__.'/../../samples';
        $zipper->add($dir);
        $this->assertEquals($zipper->getFiles(), array($dir));

        $exclude = __DIR__.'/../../samples/logs';
        $zipper->addExclude($exclude);
        $this->assertEquals($zipper->getExcludes(), array($exclude));


        $zipper->compressAs(__DIR__.'/../../temp/sweet.zip');
        $this->assertFileExists(__DIR__.'/../../temp/sweet.zip');

//        $zipper = new Zipper();
//        $zipper->setZipFileDirectoryPath(__DIR__.'');
//        $zipper->setPathToBeZipped(__DIR__);
//        $zipper->compress();

//        $this->assertInstanceOf('Dimsav\\Zipper', $zipper);
    }

}