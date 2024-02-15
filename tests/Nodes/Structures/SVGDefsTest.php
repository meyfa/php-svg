<?php

namespace SVG\Nodes\Structures;

use SVG\Nodes\SVGNode;
use SVG\Rasterization\SVGRasterizer;

/**
 * @coversDefaultClass \SVG\Nodes\Structures\SVGDefs
 * @covers ::<!public>
 *
 * @SuppressWarnings(PHPMD)
 */
class SVGDefsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers ::__construct
     */
    public function test__construct()
    {
        // should not set any attributes by default
        $obj = new SVGDefs();
        $this->assertSame([], $obj->getSerializableAttributes());
    }

    /**
     * @covers ::rasterize
     */
    public function testRasterize()
    {
        $obj = new SVGDefs();

        $mockChild = $this->getMockForAbstractClass(SVGNode::class);
        $obj->addChild($mockChild);

        $rast = $this->getMockBuilder(SVGRasterizer::class)
            ->disableOriginalConstructor()
            ->getMock();

        // should not rasterize itself or its children
        $rast->expects($this->never())->method($this->anything());
        $mockChild->expects($this->never())->method('rasterize');
        $obj->rasterize($rast);
    }
}
