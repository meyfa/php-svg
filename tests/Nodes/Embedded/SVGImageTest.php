<?php

namespace SVG;

use SVG\Nodes\Embedded\SVGImage;

/**
 * @coversDefaultClass \SVG\Nodes\Embedded\SVGImage
 * @covers ::<!public>
 *
 * @SuppressWarnings(PHPMD)
 */
class SVGImageTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers ::__construct
     */
    public function test__construct()
    {
        // should not set any attributes by default
        $obj = new SVGImage();
        $this->assertSame(array(), $obj->getSerializableAttributes());

        // should set attributes when provided
        $obj = new SVGImage('test-href', 10, 10, 100, 100);
        $this->assertSame(array(
            'xlink:href' => 'test-href',
            'x' => '10',
            'y' => '10',
            'width' => '100',
            'height' => '100',
        ), $obj->getSerializableAttributes());
    }

    /**
     * @covers ::getHref
     */
    public function testGetHref()
    {
        // should return xlink:href when available
        $obj = new SVGImage();
        $obj->setAttribute('xlink:href', 'test-xlink-href');
        $obj->setAttribute('href', 'test-href');
        $this->assertSame('test-xlink-href', $obj->getHref());

        // should return href when xlink:href not available
        $obj = new SVGImage();
        $obj->setAttribute('href', 'test-href');
        $this->assertSame('test-href', $obj->getHref());

        // should return null when no href available
        $obj = new SVGImage();
        $this->assertNull($obj->getHref());
    }

    /**
     * @covers ::setHref
     */
    public function testSetHref()
    {
        $obj = new SVGImage();

        // should set xlink:href
        $obj->setHref('test-href');
        $this->assertSame('test-href', $obj->getAttribute('xlink:href'));

        // should return same instance
        $this->assertSame($obj, $obj->setHref('test-href'));
    }

    /**
     * @covers ::getX
     */
    public function testGetX()
    {
        $obj = new SVGImage();

        // should return the attribute
        $obj->setAttribute('x', 42);
        $this->assertSame('42', $obj->getX());
    }

    /**
     * @covers ::setX
     */
    public function testSetX()
    {
        $obj = new SVGImage();

        // should update the attribute
        $obj->setX(42);
        $this->assertSame('42', $obj->getAttribute('x'));

        // should return same instance
        $this->assertSame($obj, $obj->setX(42));
    }

    /**
     * @covers ::getY
     */
    public function testGetY()
    {
        $obj = new SVGImage();

        // should return the attribute
        $obj->setAttribute('y', 42);
        $this->assertSame('42', $obj->getY());
    }

    /**
     * @covers ::setY
     */
    public function testSetY()
    {
        $obj = new SVGImage();

        // should update the attribute
        $obj->setY(42);
        $this->assertSame('42', $obj->getAttribute('y'));

        // should return same instance
        $this->assertSame($obj, $obj->setY(42));
    }

    /**
     * @covers ::getWidth
     */
    public function testGetWidth()
    {
        $obj = new SVGImage();

        // should return the attribute
        $obj->setAttribute('width', 42);
        $this->assertSame('42', $obj->getWidth());
    }

    /**
     * @covers ::setWidth
     */
    public function testSetWidth()
    {
        $obj = new SVGImage();

        // should update the attribute
        $obj->setWidth(42);
        $this->assertSame('42', $obj->getAttribute('width'));

        // should return same instance
        $this->assertSame($obj, $obj->setWidth(42));
    }

    /**
     * @covers ::getHeight
     */
    public function testGetHeight()
    {
        $obj = new SVGImage();

        // should return the attribute
        $obj->setAttribute('height', 42);
        $this->assertSame('42', $obj->getHeight());
    }

    /**
     * @covers ::setHeight
     */
    public function testSetHeight()
    {
        $obj = new SVGImage();

        // should update the attribute
        $obj->setHeight(42);
        $this->assertSame('42', $obj->getAttribute('height'));

        // should return same instance
        $this->assertSame($obj, $obj->setHeight(42));
    }

    /**
     * @covers ::rasterize
     */
    public function testRasterize()
    {
        $obj = new SVGImage('test-href', 10, 10, 100, 100);

        $rast = $this->getMockBuilder('\SVG\Rasterization\SVGRasterizer')
            ->disableOriginalConstructor()
            ->getMock();

        // should call image renderer with correct options
        $rast->expects($this->once())->method('render')->with(
            $this->identicalTo('image'),
            $this->identicalTo(array(
                'href' => 'test-href',
                'x' => '10',
                'y' => '10',
                'width' => '100',
                'height' => '100',
            )),
            $this->identicalTo($obj)
        );
        $obj->rasterize($rast);

        // should not rasterize with 'display: none' style
        $obj->setStyle('display', 'none');
        $obj->rasterize($rast);

        // should not rasterize with 'visibility: hidden' or 'collapse' style
        $obj->setStyle('display', null);
        $obj->setStyle('visibility', 'hidden');
        $obj->rasterize($rast);
        $obj->setStyle('visibility', 'collapse');
        $obj->rasterize($rast);
    }
}
