<?php

use JangoBrick\SVG\SVGImage;

class SVGImageTest extends PHPUnit_Framework_TestCase
{
    private $xml;

    public function setUp()
    {
        $this->xml  = '<?xml version="1.0" encoding="utf-8"?>';
        $this->xml .= '<svg width="10" height="10" xmlns="http://www.w3.org/2000/svg">';
        $this->xml .= '</svg>';
    }

    public function testGetDocument()
    {
        $image = SVGImage::fromString($this->xml);

        $doc = $image->getDocument();
        $docFragClass = '\JangoBrick\SVG\Nodes\Structures\SVGDocumentFragment';
        $this->assertInstanceOf($docFragClass, $doc);
    }

    public function testToRasterImage()
    {
        $image = SVGImage::fromString($this->xml);

        $rasterImage = $image->toRasterImage(100, 100);
        $this->assertTrue(is_resource($rasterImage));
        $this->assertSame('gd', get_resource_type($rasterImage));

        $this->assertSame(100, imagesx($rasterImage));
        $this->assertSame(100, imagesy($rasterImage));
    }

    public function testToXMLString()
    {
        $image = SVGImage::fromString($this->xml);

        $this->assertSame($this->xml, $image->toXMLString());
    }
}
