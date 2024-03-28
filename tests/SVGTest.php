<?php

namespace SVG\Tests;

use PHPUnit\Framework\TestCase;
use SVG\Nodes\Structures\SVGDocumentFragment;
use SVG\SVG;

/**
 * @coversDefaultClass \SVG\SVG
 * @covers ::<!public>
 *
 * @SuppressWarnings(PHPMD)
 */
class SVGTest extends TestCase
{
    private $xml;
    private $xmlNoDeclaration;

    public function setUp(): void
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
    public function testConstructSetsDocumentDimensions(): void
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
    public function testGetDocument(): void
    {
        $image = new SVG();
        $doc = $image->getDocument();

        // should be instanceof the correct class
        $docFragClass = SVGDocumentFragment::class;
        $this->assertInstanceOf($docFragClass, $doc);

        // should be set to root
        $this->assertTrue($doc->isRoot());
    }

    /**
     * @requires extension gd
     * @covers ::toRasterImage
     */
    public function testToRasterImage(): void
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
    public function test__toString(): void
    {
        $image = new SVG(37, 42);

        // should return correctly stringified XML
        $this->assertSame($this->xml, (string) $image);
    }

    /**
     * @covers ::toXMLString
     */
    public function testToXMLString(): void
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
    public function testFromString(): void
    {
        $image = SVG::fromString($this->xml);
        $doc = $image->getDocument();

        // should return an instance of SVG
        $this->assertInstanceOf(SVG::class, $image);

        // should have correct width and height
        $this->assertSame('37', $doc->getWidth());
        $this->assertSame('42', $doc->getHeight());

        // should succeed without xml declaration
        $image = SVG::fromString($this->xmlNoDeclaration);
        $doc = $image->getDocument();
        $this->assertInstanceOf(SVG::class, $image);
        $this->assertSame('37', $doc->getWidth());
        $this->assertSame('42', $doc->getHeight());
    }

    /**
     * @covers ::fromFile
     */
    public function testFromFile(): void
    {
        $image = SVG::fromFile(__DIR__ . '/php_test.svg');

        $this->assertInstanceOf(SVG::class, $image);
    }
}
