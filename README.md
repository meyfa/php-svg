# php-svg
This is a vector graphics library for PHP, which surely is a broad
specification. That is due to the fact that the goal of this project is to
offer features in three different, big areas:

- Generating SVG images from PHP code and outputting them, either into XML
    strings or into files.
- Loading and parsing XML strings into document trees that can be easily
    modified and then also turned back into strings.
- Transforming parsed or generated document trees into raster graphics,
    like PNG.



## Contributing

Especially the third goal will take a lot of time and effort, so you are welcome
to contribute if this is a project you are interested in.  
In case you decide to contribute, please honor these two simple guidelines:

1. External libraries shall not be used.
2. Please set your editor to use 4 spaces for indentation. In general, it would
    be good to follow the existing code style for consistency.



## Getting Started

### Creating an image

The following code generates a SVG with a blue square, sets the Content-Type
header and echoes it:

```php
include 'php-svg/SVG.php';

$image = new SVGImage();
$doc = $image->getDocument();

// blue 40x40 square at (0, 0)
$square = new SVGRect(0, 0, 40, 40);
$square->setStyle('fill', '#0000FF');
$doc->addChild($square);

header('Content-Type: image/svg+xml');
echo $image;
```
