# PHP-SVG

**The zero-dependency vector graphics library for PHP applications**

[![CI](https://github.com/meyfa/php-svg/actions/workflows/php.yml/badge.svg)](https://github.com/meyfa/php-svg/actions/workflows/php.yml)
[![Maintainability](https://api.codeclimate.com/v1/badges/8f73468601a653aff0e8/maintainability)](https://codeclimate.com/github/meyfa/php-svg/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/8f73468601a653aff0e8/test_coverage)](https://codeclimate.com/github/meyfa/php-svg/test_coverage)


## Features

PHP-SVG can help with the following set of tasks:

* Generating vector graphics programmatically with PHP code. The resulting SVG string can be written to a file or sent
  as part of the HTTP response.
* Loading and parsing SVG images into document trees that are easy to modify and append.
* Converting vector graphics into raster image formats, such as PNG or JPEG (rasterization).

Please note that PHP-SVG is still in its alpha stage and may not be suitable for complex applications, yet.
Contributions are very welcome. [Find out how to contribute](#contributing).


## Installation

### Requirements

PHP-SVG is free of dependencies. All it needs is a PHP installation satisfying the following requirements:

* PHP version 5.3.3 or newer. This library is tested against all versions up to PHP 8.
* If you wish to load SVG files, or strings containing SVG code, you need to have the
  ['simplexml' PHP extension](https://www.php.net/manual/en/book.simplexml.php).
* If you wish to use the rasterization feature for converting SVGs to raster images (PNGs, JPEGs, ...), you need to
  have the ['gd' PHP extension](https://www.php.net/manual/en/book.image.php).

These extensions are almost always available on typical PHP installations. Still, you might want to make sure that your
hosting provider actually has them. If SimpleXML or GD are missing, PHP-SVG can still perform all the tasks except those
that require the missing extension. For example, programmatically generating SVG images and outputting them as XML will
always work, even without any extension.

### Composer (recommended)

This package is available on [Packagist](https://packagist.org/packages/meyfa/php-svg) and can be installed with
Composer:

```
$ composer require meyfa/php-svg
```

### Manual installation

Download the [latest release](https://github.com/meyfa/php-svg/releases) or the current
[development version](https://github.com/meyfa/php-svg/archive/refs/heads/main.zip) of this repository, and extract the
source code somewhere in your project. Then you can use our own autoloader to make available the SVG namespace:

```php
<?php
require_once __DIR__.'/<path_to_php_svg>/autoloader.php';
```

The rest works exactly the same as with Composer, just without the benefits of a package manager.


## Getting Started

### Creating an image

The following code generates an SVG with a blue square, sets the Content-Type
header and echoes it:

```php
<?php

use SVG\SVG;
use SVG\Nodes\Shapes\SVGRect;

// image with 100x100 viewport
$image = new SVG(100, 100);
$doc = $image->getDocument();

// blue 40x40 square at (0, 0)
$square = new SVGRect(0, 0, 40, 40);
$square->setStyle('fill', '#0000FF');
$doc->addChild($square);

header('Content-Type: image/svg+xml');
echo $image;
```

### Rasterizing

To convert an instance of `SVG` to a PHP/GD image resource, or in other words
convert it to a raster image, you simply call
`toRasterImage($width, $height [, $background])` on it. Example:

```php
<?php

use SVG\SVG;
use SVG\Nodes\Shapes\SVGCircle;

$image = new SVG(100, 100);
$doc = $image->getDocument();

// circle with radius 20 and green border, center at (50, 50)
$doc->addChild(
    (new SVGCircle(50, 50, 20))
        ->setStyle('fill', 'none')
        ->setStyle('stroke', '#0F0')
        ->setStyle('stroke-width', '2px')
);

// rasterize to a 200x200 image, i.e. the original SVG size scaled by 2.
// the background will be transparent by default.
$rasterImage = $image->toRasterImage(200, 200);

header('Content-Type: image/png');
imagepng($rasterImage);
```

If you require a specific background color, e.g. white, use the 3rd parameter.
It supports all CSS colors (including named colors, hexadecimal, rgba, etc.):

```php
<?php
$rasterImage = $image->toRasterImage(200, 200, '#FFFFFF');
```

Specifying a background color is mandatory for JPEG output, as JPEG does not
support transparency.

### Loading an SVG

You can load SVG images both from strings and from files. This example loads one
from a string, moves the contained rectangle and echoes the new SVG:

```php
<?php

use SVG\SVG;

$svg  = '<svg width="100" height="100">';
$svg .= '<rect width="50" height="50" fill="#00F" />';
$svg .= '</svg>';

$image = SVG::fromString($svg);
$doc = $image->getDocument();

$rect = $doc->getChild(0);
$rect->setX(25)->setY(25);

header('Content-Type: image/svg+xml');
echo $image;
```

For loading from a file instead, you would call `SVG::fromFile($file)`.
That function supports local file paths as well as URLs.

For additional documentation, see the [wiki](https://github.com/meyfa/php-svg/wiki).


## Contributing

This project is available to the community for free, and there is still a lot of work to do.
Every bit of help is welcome! You can contribute in the following ways:

* **By giving PHP-SVG a star on GitHub to spread the word.** The more it's used, the better it will become!
* **By reporting any bugs or missing features you encounter.** Please quickly search through the
  [issues tab](https://github.com/meyfa/php-svg/issues) to see whether someone has already reported the same problem.
  Maybe you can add something to the discussion?
* **By contributing code.** You can take a look at the currently open issues to see what needs to be worked on.
  If you encounter a bug that you know how to resolve, it should be pretty safe to submit the fix as a pull request
  directly. For new features, it is recommended to first create an issue for your proposal, to gather feedback and
  discuss implementation strategies.

**Please read:**

By contributing material/code to PHP-SVG via a pull request or commit, you confirm that you own the necessary rights to
that material/code, and agree to make available that material/code under the terms of the MIT license.
