<?php

use SVG\SVGImage;

/**
 * @SuppressWarnings(PHPMD)
 */
class SVGImageTest extends PHPUnit_Framework_TestCase
{
    private $xml;

    public function setUp()
    {
        $this->xml  = '<?xml version="1.0" encoding="utf-8"?>';
        $this->xml .= '<svg width="37" height="42" xmlns="http://www.w3.org/2000/svg" '.
            'xmlns:xlink="http://www.w3.org/1999/xlink">';
        $this->xml .= '</svg>';
    }

    public function testGetDocument()
    {
        $image = new SVGImage(37, 42);
        $doc = $image->getDocument();

        // should be instanceof the correct class
        $docFragClass = '\SVG\Nodes\Structures\SVGDocumentFragment';
        $this->assertInstanceOf($docFragClass, $doc);

        // should be set to root
        $this->assertTrue($doc->isRoot());

        // should have correct width and height
        $this->assertSame('37', $doc->getWidth());
        $this->assertSame('42', $doc->getHeight());
    }

    public function testToRasterImage()
    {
        $image = new SVGImage(37, 42);
        $rasterImage = $image->toRasterImage(100, 200);

        // should be a gd resource
        $this->assertTrue(is_resource($rasterImage));
        $this->assertSame('gd', get_resource_type($rasterImage));

        // should have correct width and height
        $this->assertSame(100, imagesx($rasterImage));
        $this->assertSame(200, imagesy($rasterImage));
    }

    public function test__toString()
    {
        $image = new SVGImage(37, 42);

        // should return correctly stringified XML
        $this->assertSame($this->xml, (string) $image);
    }

    public function testToXMLString()
    {
        $image = new SVGImage(37, 42);

        // should return correctly stringified XML
        $this->assertSame($this->xml, $image->toXMLString());
    }

    public function testFromString()
    {
        $image = SVGImage::fromString($this->xml);
        $doc = $image->getDocument();

        // should return an instance of SVGImage
        $this->assertInstanceOf('\SVG\SVGImage', $image);

        // should have correct width and height
        $this->assertSame('37', $doc->getWidth());
        $this->assertSame('42', $doc->getHeight());
    }
}
