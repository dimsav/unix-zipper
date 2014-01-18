# UnixZipper

[![Latest Stable Version](https://poser.pugx.org/dimsav/unix-zipper/v/stable.png)](https://packagist.org/packages/dimsav/unix-zipper) [![Build Status](https://travis-ci.org/laravel/framework.png)](https://travis-ci.org/dimsav/unix-zipper)

A simple zip compression library for Unix operating systems.
UnixZipper is ideal for creating backups of your projects in unix servers.

## Features
1. Easy to use
2. Password protection
2. Tested for stability

## How does it work

Here is a simple example. Feel free to check the tests to see the class in action.

```php
// Instantiate the class
$zipper = new UnixZipper();

// Add absolute paths of directories or files for compression
$zipper->add('/absolute/path/to/some/directory');
$zipper->add('/absolute/path/to/file.txt');

// Exclude directories and files
$zipper->exclude('/absolute/path/to/some/directory');
$zipper->exclude('/absolute/path/to/some/file.txt');

// Add a password if you wish
$zipper->setPassword('my_password');

// The path of the file that will be generated
// If the given path doesn't exist, it will be created automatically.
$zipper->setDestination('/file/after/compression/test.zip');

// Do the magic
$zipper->compress();
```

Since version 1.2, you can set a base path, and provide the files to be compressed relatively.

```php
$zipper = new UnixZipper();

// Set base path
$zipper->setAbsolutePathAsBase('/absolute/projects');

// Add relative paths of directories or files for compression
$zipper->add('project-1');     // /absolute/projects/project-1
$zipper->add('logs/file.txt'); // /absolute/projects/logs/file.txt

$zipper->setDestination('/file/after/compression/test.zip');

// Compress
$zipper->compress();
```

## Why unix

The reason I chose to make this package unix-only is because I wanted to rely
on the system's zip function, that offers stability and flexibility. It also
offers the possibility to exclude directories recursively, a feature I couldn't
find in other php classes.


## Installation

Install using composer:

1. Add `"dimsav/unix-zipper": "1.*"` to your composer.json file
2. Run `composer update`

## Dependencies

The only requirements are: 
* executing the code on a unix system 
* composer for installing/autoloading
