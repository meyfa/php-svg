<?php

namespace SVG;

use SVG\SVG;

/**
 * @coversDefaultClass \SVG\SVG
 * @covers ::<!public>
 *
 * @SuppressWarnings(PHPMD)
 */
class SVGTest extends \PHPUnit\Framework\TestCase
{
    private $xml;
    private $xmlNoDeclaration;

    public function setUp()
    {
        $this->xml  = '<?xml version="1.0" encoding="utf-8"?>';
        $this->xml .= '<svg ' .
            'xmlns="http://www.w3.org/2000/svg" ' .
            'xmlns:xlink="http://www.w3.org/1999/xlink" ' .
            'width="37" height="42" />';

        $this->xmlNoDeclaration = '<svg ' .
            'xmlns="http://www.w3.org/2000/svg" ' .
            'xmlns:xlink="http://www.w3.org/1999/xlink" ' .
            'width="37" height="42" />';
    }

    /**
     * @covers ::__construct
     */
    public function testConstructSetsDocumentDimensions()
    {
        $image = new SVG();
        $doc = $image->getDocument();
        $this->assertNull($doc->getWidth());
        $this->assertNull($doc->getHeight());

        $image = new SVG(37, 42);
        $doc = $image->getDocument();
        $this->assertSame('37', $doc->getWidth());
        $this->assertSame('42', $doc->getHeight());
    }

    /**
     * @covers ::getDocument
     */
    public function testGetDocument()
    {
        $image = new SVG();
        $doc = $image->getDocument();

        // should be instanceof the correct class
        $docFragClass = '\SVG\Nodes\Structures\SVGDocumentFragment';
        $this->assertInstanceOf($docFragClass, $doc);

        // should be set to root
        $this->assertTrue($doc->isRoot());
    }

    /**
     * @requires extension gd
     * @covers ::toRasterImage
     */
    public function testToRasterImage()
    {
        $image = new SVG(37, 42);
        $rasterImage = $image->toRasterImage(100, 200);

        if (class_exists('\GdImage', false)) {
            // PHP >=8: should be an image object
            $this->assertInstanceOf('\GdImage', $rasterImage);
        } else {
            // PHP <8: should be a gd resource
            $this->assertTrue(is_resource($rasterImage));
            $this->assertSame('gd', get_resource_type($rasterImage));
        }

        // should have correct width and height
        $this->assertSame(100, imagesx($rasterImage));
        $this->assertSame(200, imagesy($rasterImage));
    }

    /**
     * @covers ::__toString
     */
    public function test__toString()
    {
        $image = new SVG(37, 42);

        // should return correctly stringified XML
        $this->assertSame($this->xml, (string) $image);
    }

    /**
     * @covers ::toXMLString
     */
    public function testToXMLString()
    {
        $image = new SVG(37, 42);

        // should return correctly stringified XML
        $this->assertSame($this->xml, $image->toXMLString());

        // should respect standalone=false
        $this->assertSame($this->xmlNoDeclaration, $image->toXMLString(false));
    }

    /**
     * @covers ::fromString
     */
    public function testFromString()
    {
        $image = SVG::fromString($this->xml);
        $doc = $image->getDocument();

        // should return an instance of SVG
        $this->assertInstanceOf('\SVG\SVG', $image);

        // should have correct width and height
        $this->assertSame('37', $doc->getWidth());
        $this->assertSame('42', $doc->getHeight());

        // should succeed without xml declaration
        $image = SVG::fromString($this->xmlNoDeclaration);
        $doc = $image->getDocument();
        $this->assertInstanceOf('\SVG\SVG', $image);
        $this->assertSame('37', $doc->getWidth());
        $this->assertSame('42', $doc->getHeight());
    }

    /**
     * @covers ::fromFile
     */
    public function testFromFile()
    {
        $image = SVG::fromFile(__DIR__ . '/php_test.svg');

        $this->assertInstanceOf('\SVG\SVG', $image);
    }
}
