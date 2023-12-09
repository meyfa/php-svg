# PHP-SVG

**The zero-dependency vector graphics library for PHP applications**

[![CI](https://github.com/meyfa/php-svg/actions/workflows/php.yml/badge.svg)](https://github.com/meyfa/php-svg/actions/workflows/php.yml)
[![Maintainability](https://api.codeclimate.com/v1/badges/8f73468601a653aff0e8/maintainability)](https://codeclimate.com/github/meyfa/php-svg/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/8f73468601a653aff0e8/test_coverage)](https://codeclimate.com/github/meyfa/php-svg/test_coverage)
![Packagist PHP Version](https://img.shields.io/packagist/dependency-v/meyfa/php-svg/php?style=plastic)
[![Packagist Downloads](https://img.shields.io/packagist/dt/meyfa/php-svg)](https://packagist.org/packages/meyfa/php-svg)


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

* PHP version 7.3 or newer. This library is tested against all versions up to (and including) PHP 8.3.
* If you wish to load SVG files, or strings containing SVG code, you need to have the
  ['simplexml' PHP extension](https://www.php.net/manual/en/book.simplexml.php).
* If you wish to use the rasterization feature for converting SVGs to raster images (PNGs, JPEGs, ...), you need to
  have the ['gd' PHP extension](https://www.php.net/manual/en/book.image.php).

These extensions are almost always available on typical PHP installations. Still, you might want to make sure that your
hosting provider actually has them. If SimpleXML or GD are missing, PHP-SVG can still perform all tasks except those
that require the missing extension. For example, programmatically generating SVG images and outputting them as XML will
always work, even without any extension.

### Composer (recommended)

This package is available on [Packagist](https://packagist.org/packages/meyfa/php-svg) and can be installed with
Composer:

```
$ composer require meyfa/php-svg
```

This adds a dependency to your composer.json file. If you haven't already, setup autoloading for Composer dependencies
by adding the following `require` statement at the top of your PHP scripts:

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';
```

You may need to adjust the path if your script is located in another folder.

### Manual installation

Download the [latest release](https://github.com/meyfa/php-svg/releases) or the current
[development version](https://github.com/meyfa/php-svg/archive/refs/heads/main.zip) of this repository, and extract the
source code somewhere in your project. Then you can use our own autoloader to make available the SVG namespace:

```php
<?php
require_once __DIR__ . '/<path_to_php_svg>/autoloader.php';
```

The rest works exactly the same as with Composer, just without the benefits of a package manager.


## Getting started

This section contains a few simple examples to get you started with PHP-SVG.

### Creating SVGs programmatically

The following code generates an SVG containing a blue square. It then sets the Content-Type header and sends the SVG as
text to the client:

```php
<?php
require __DIR__ . '/vendor/autoload.php';

use SVG\SVG;
use SVG\Nodes\Shapes\SVGRect;

// image with dimensions 100x100
$image = new SVG(100, 100);
$doc = $image->getDocument();

// blue 40x40 square at the origin
$square = new SVGRect(0, 0, 40, 40);
$square->setStyle('fill', '#0000FF');
$doc->addChild($square);

header('Content-Type: image/svg+xml');
echo $image;
```

### Converting SVGs to strings

The above example uses `echo`, which implicitly converts the SVG to a string containing its XML representation. This
conversion can also be made explicitly:

```php
// This will include the leading <?xml ... ?> tag needed for standalone SVG files:
$xmlString = $image->toXMLString();
file_put_contents('my-image.svg', $xmlString);

// This will omit the <?xml ... ?> tag for outputting directly into HTML:
$xmlString = $image->toXMLString(false);
echo '<div class="svg-container">' . $xmlString . '</div>';
```

### Loading SVGs from strings

In addition to generating images programmatically from scratch, they can be parsed from strings or loaded from files.
The following code parses an SVG string, mutates part of the image, and echoes the result to the client:

```php
<?php
require __DIR__ . '/vendor/autoload.php';

use SVG\SVG;

$svg  = '<svg width="100" height="100">';
$svg .= '<rect width="40" height="40" fill="#00F" id="my-rect" />';
$svg .= '</svg>';

$image = SVG::fromString($svg);
$rect = $image->getDocument()->getElementById('my-rect');
$rect->setX(25)->setY(25);

header('Content-Type: image/svg+xml');
echo $image;
```

### Loading SVGs from files

For loading from a file instead of a string, call `SVG::fromFile($file)`. That function supports paths on the local
file system, as well as remote URLs. For example:

```php
// load from the local file system:
$image = SVG::fromFile('path/to/file.svg');

// or from the web (worse performance due to HTTP request):
$image = SVG::fromFile('https://upload.wikimedia.org/wikipedia/commons/8/8c/Even-odd_and_non-zero_winding_fill_rules.svg');
```

### Rasterizing

> :warning: **This feature in particular is very much work-in-progress.**
> Many things will look wrong and rendering large images may be very slow.

The `toRasterImage($width, $height [, $background])` method is used to render an SVG to a raster image. The result is
a GD image resource. GD then provides methods for encoding this resource to a number of formats:

* [imagepng](https://www.php.net/manual/en/function.imagepng.php)
* [imagejpeg](https://www.php.net/manual/en/function.imagejpeg.php)
* [imagebmp](https://www.php.net/manual/en/function.imagebmp.php)
* [imagegif](https://www.php.net/manual/en/function.imagegif.php)
* [imagewebp](https://www.php.net/manual/en/function.imagewebp.php)
* [imageavif](https://www.php.net/manual/en/function.imageavif.php) (requires PHP 8.1.0 or newer)

```php
<?php
require __DIR__ . '/vendor/autoload.php';

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

The raster image will default to preserving any transparency present in the SVG. For cases where an opaque image is
desired instead, it is possible to specify a background color. This may be mandatory when outputting to some formats,
such as JPEG, that cannot encode alpha channel information. For example:

```php
// white background
$rasterImage = $image->toRasterImage(200, 200, '#FFFFFF');
imagejpeg($rasterImage, 'path/to/output.jpg');
```

### Text rendering (loading fonts)

PHP-SVG implements support for TrueType fonts (`.ttf` files) using a handcrafted TTF parser. Since PHP doesn't come
with any built-in font files, you will need to provide your own. The following example shows how to load a set of font
files. PHP-SVG will try to pick the best matching font for a given text element, based on algorithms from the CSS spec.

```php
<?php
require __DIR__ . '/vendor/autoload.php';

use SVG\SVG;

// load a set of fonts from the "fonts" directory relative to the script directory
SVG::addFont(__DIR__ . '/fonts/Ubuntu-Regular.ttf');
SVG::addFont(__DIR__ . '/fonts/Ubuntu-Bold.ttf');
SVG::addFont(__DIR__ . '/fonts/Ubuntu-Italic.ttf');
SVG::addFont(__DIR__ . '/fonts/Ubuntu-BoldItalic.ttf');

$image = SVG::fromString('
<svg width="220" height="220">
  <rect x="0" y="0" width="100%" height="100%" fill="lightgray"/>
  <g font-size="15">
    <text y="20">hello world</text>
    <text y="100" font-weight="bold">in bold!</text>
    <text y="120" font-style="italic">and italic!</text>
    <text y="140" font-weight="bold" font-style="italic">bold and italic</text>
  </g>
</svg>
');

header('Content-Type: image/png');
imagepng($image->toRasterImage(220, 220));
```

Note that PHP often behaves unexpectedly when using relative paths, especially with fonts. Hence, it is recommended to
use absolute paths, or use the `__DIR__` constant to prepend the directory of the current script.


## Document model

This section explains a bit more about the object structure and how to query or mutate parts of documents.

### SVG root node

An instance of the `SVG` class represents the image abstractly. It does not store DOM information itself. That is the
responsibility of the document root node, which is the object corresponding to the `<svg>` XML tag. For example:

```php
<?php
require __DIR__ . '/vendor/autoload.php';

use SVG\SVG;

$svg  = '<svg width="100" height="100">';
$svg .= '<rect width="40" height="40" fill="#00F" id="my-rect" />';
$svg .= '</svg>';

$image = SVG::fromString($svg);

// obtain the <svg> node (an instance of \SVG\SVGDocumentFragment):
$doc = $image->getDocument();
```

### Child nodes

Child nodes of any element can be obtained as follows:

```php
// Returns the number of children.
$numberOfChildren = $element->countChildren();

// Returns a child element by its index.
$firstChild = $element->getChild(0);

// Returns an array of matching child elements.
$childrenThatAreRects = $element->getElementsByTagName('rect');

// Returns an array of matching child elements.
$childrenWithClass = $element->getElementsByClassName('my-class-name');
```

The root node has an additional function for obtaining a unique element by its `id` attribute:

```
// Returns an element or null.
$element = $doc->getElementById('my-rect');
```

Child nodes can be added, removed and replaced:

```php
// Append a child at the end.
$doc->addChild(new \SVG\Nodes\Shapes\SVGLine(0, 0, 10, 10));

// Insert a new child between the children at positions 0 and 1.
$g = new \SVG\Nodes\Structures\SVGGroup();
$doc->addChild($g, 1);

// Remove the second child. (equivalent to $doc->removeChild($g);)
$doc->removeChild(1);

// replace the first child with $g
$doc->setChild(0, $g);
```

### Attributes

Every attribute is accessible via `$element->getAttribute($name)`. Some often-used attributes have additional shortcut
methods, but they are only available on the classes for which they are valid, so make sure the node you are accessing
is of the correct type to prevent runtime errors. Some examples:

```php
$doc->getWidth();
// equivalent to: $doc->getAttribute('width')

$doc->setWidth('200px');
// equivalent to: $doc->setAttribute('width', '200px');

$rect->setRX('10%');
// equivalent to: $rect->setAttribute('rx', '10%');
```

### Styles

Some presentation attributes are considered style properties by the SVG spec. These will be treated specially by
PHP-SVG and made available via `getStyle` and `setStyle` instead of `getAttribute` and `setAttribute`.
Consider the following node:

```xml
<circle x="10" y="10" r="5" style="fill: #red" stroke="blue" stroke-width="4" />
```

* `x`, `y` and `r` are attributes.
* `fill`, `stroke` and `stroke-width` are styles.


## Debugging

If you aren't getting any output but only a blank page, try temporarily enabling PHP's error reporting to find the cause
of the problem.

```php
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
// ... rest of the script ...
```

Make sure you disable this again when you're done, as it may leak sensitive information about your server setup.
Additionally, ensure you're not setting the `Content-Type` header to an image format in this mode, as your browser will
try to render the error message as an image, which won't work.

Alternatively, you may attempt to find your server's error log file. Its location depends on how you're running PHP.


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

By contributing material or code to PHP-SVG via a pull request or commit, you confirm that you own the necessary rights
to that material or code, and agree to make available that material or code under the terms of the MIT license.
