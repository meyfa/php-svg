<?php

namespace SVG\Tests\Nodes\Structures;

use PHPUnit\Framework\TestCase;
use SVG\Nodes\Structures\SVGStyle;
use SVG\Rasterization\SVGRasterizer;

/**
 * @coversDefaultClass \SVG\Nodes\Structures\SVGStyle
 *
 * @SuppressWarnings(PHPMD)
 */
class SVGStyleTest extends TestCase
{
    /**
     * @covers ::__construct
     */
    public function test__construct(): void
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
    public function testGetType(): void
    {
        $obj = new SVGStyle();
        $obj->setAttribute('type', 'test-type');

        $this->assertSame('test-type', $obj->getType());
    }

    /**
     * @covers ::setType
     */
    public function testSetType(): void
    {
        $obj = new SVGStyle();
        $this->assertInstanceOf(SVGStyle::class, $obj->setType('test-type'));

        $this->assertEquals('test-type', $obj->getAttribute('type'));

        $this->assertSame($obj, $obj->setType('foo'));
    }

    /**
     * @covers ::rasterize
     */
    public function testRasterize(): void
    {
        $obj = new SVGStyle();

        $rast = $this->getMockBuilder(SVGRasterizer::class)
            ->disableOriginalConstructor()
            ->getMock();

        // should not manipulate anything
        $rast->expects($this->never())->method($this->anything());
        $obj->rasterize($rast);
    }
}
