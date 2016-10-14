<?php

use PHPUnit\Framework\TestCase;

use JangoBrick\SVG\SVGImage;
use JangoBrick\SVG\Nodes\Structures\SVGDocumentFragment;

class SVGImageTest extends TestCase
{
    private static $xml = '<?xml version="1.0" encoding="utf-8"?>'.
        '<svg xmlns="http://www.w3.org/2000/svg" width="10" height="10"></svg>';

    public function testGetDocument()
    {
        $image = SVGImage::fromString(self::$xml);

        $doc = $image->getDocument();
        $this->assertInstanceOf(SVGDocumentFragment::class, $doc);
    }

    public function testToRasterImage()
    {
        $image = SVGImage::fromString(self::$xml);

        $rasterImage = $image->toRasterImage(100, 100);
        $this->assertTrue(is_resource($rasterImage));
        $this->assertSame('gd', get_resource_type($rasterImage));

        $this->assertSame(100, imagesx($rasterImage));
        $this->assertSame(100, imagesy($rasterImage));
    }

    public function testToXMLString()
    {
        $image = SVGImage::fromString(self::$xml);

        $this->assertSame(self::$xml, $image->toXMLString());
    }
}
