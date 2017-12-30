<?php

use SVG\SVGImage;

class SVGImageTest extends PHPUnit_Framework_TestCase
{
    private $xml;

    public function setUp()
    {
        $this->xml  = '<?xml version="1.0" encoding="utf-8"?>';
        $this->xml .= '<svg width="10" height="10" xmlns="http://www.w3.org/2000/svg" '.
            'xmlns:xlink="http://www.w3.org/1999/xlink">';
        $this->xml .= '</svg>';
    }

    public function testGetDocument()
    {
        $image = new SVGImage(10, 10);
        $doc = $image->getDocument();

        $docFragClass = '\SVG\Nodes\Structures\SVGDocumentFragment';
        $this->assertInstanceOf($docFragClass, $doc);

        $this->assertTrue($doc->isRoot());

        $this->assertEquals('10', $doc->getWidth());
        $this->assertEquals('10', $doc->getHeight());
    }

    public function testToRasterImage()
    {
        $image = new SVGImage(10, 10);
        $rasterImage = $image->toRasterImage(100, 100);

        $this->assertTrue(is_resource($rasterImage));
        $this->assertSame('gd', get_resource_type($rasterImage));

        $this->assertSame(100, imagesx($rasterImage));
        $this->assertSame(100, imagesy($rasterImage));
    }

    /**
     * @SuppressWarnings("camelCase")
     */
    public function test__toString()
    {
        $image = new SVGImage(10, 10);

        $this->assertSame($this->xml, (string) $image);
    }

    public function testToXMLString()
    {
        $image = new SVGImage(10, 10);

        $this->assertSame($this->xml, $image->toXMLString());
    }

    public function testFromString()
    {
        $image = SVGImage::fromString($this->xml);
        $doc = $image->getDocument();

        $this->assertInstanceOf('\SVG\SVGImage', $image);

        $this->assertEquals('10', $doc->getWidth());
        $this->assertEquals('10', $doc->getHeight());
    }
}
