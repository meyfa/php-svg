<?php

namespace SVG;

use SVG\Nodes\Structures\SVGStyle;

/**
 * @coversDefaultClass \SVG\Nodes\Structures\SVGStyle
 *
 * @SuppressWarnings(PHPMD)
 */
class SVGStyleTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers ::__construct
     */
    public function test__construct()
    {
        // should default to empty CSS, type = text/css
        $obj = new SVGStyle();
        $this->assertSame('', $obj->getValue());
        $this->assertSame('text/css', $obj->getType());

        // should allow setting CSS and type
        $obj = new SVGStyle('svg {background:beige;}', 'test-type');
        $this->assertSame('svg {background:beige;}', $obj->getValue());
        $this->assertSame('test-type', $obj->getType());
    }

    /**
     * @covers ::getType
     */
    public function testGetType()
    {
        $obj = new SVGStyle();
        $obj->setAttribute('type', 'test-type');

        $this->assertSame('test-type', $obj->getType());
    }

    /**
     * @covers ::setType
     */
    public function testSetType()
    {
        $obj = new SVGStyle();
        $this->assertInstanceOf('SVG\Nodes\Structures\SVGStyle', $obj->setType('test-type'));

        $this->assertEquals('test-type', $obj->getAttribute('type'));

        $this->assertSame($obj, $obj->setType('foo'));
    }

    /**
     * @covers ::rasterize
     */
    public function testRasterize()
    {
        $obj = new SVGStyle();

        $rast = $this->getMockBuilder('\SVG\Rasterization\SVGRasterizer')
            ->disableOriginalConstructor()
            ->getMock();

        // should not manipulate anything
        $rast->expects($this->never())->method($this->anything());
        $obj->rasterize($rast);
    }
}
