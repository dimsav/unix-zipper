# UnixZipper

A simple compression package for Unix operating systems.
UnixZipper is ideal for creating backups of your projects in unix servers.


## Who does it work

Here is a simple example but you can check the tests to see the class in action.

```php
// Instantiate the class
$zipper = new Zipper();

// Add directories and files for compression
$zipper->add('/absolute/path/to/some/directory');
$zipper->add('/absolute/path/to/file.txt');

// Exclude directories and files
$zipper->exclude('/absolute/path/to/some/directory');
$zipper->exclude('/absolute/path/to/some/file.txt');

// Add a password if you wish
$zipper->setPassword('my_password');

// The path of the file that will be generated
$zipper->setDestination('/file/after/compression/test.zip');

// Do the magic
$zipper->compress();
```

## Why unix

The reason I chose to make this package unix only is because I wanted to rely
on the system's zip function, that offers stability and flexibility.


## Installation

Even though you can just download and include the UnixZipper class, I would
recommend you to use composer, as it makes the update process much easier.

### Simple install

```php
// Installing by including the UnixZipper class
require_once('dir/to/UnixZipper.php');
```

### Install with composer on a new project

1. Install composer and add it to your system's path.
2. Clone the UnixZipper repository
3. Run `composer install` to the root directory
4. Include the `vendor/autoload.php` to your project.


## Dependencies

There are no dependencies on other packages or classes.