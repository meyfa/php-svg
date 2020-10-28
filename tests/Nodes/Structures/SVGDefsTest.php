<?php

namespace SVG;

use SVG\Nodes\Structures\SVGDefs;

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
        $this->assertSame(array(), $obj->getSerializableAttributes());
    }

    /**
     * @covers ::rasterize
     */
    public function testRasterize()
    {
        $obj = new SVGDefs();

        $mockChild = $this->getMockForAbstractClass('\SVG\Nodes\SVGNode');
        $obj->addChild($mockChild);

        $rast = $this->getMockBuilder('\SVG\Rasterization\SVGRasterizer')
            ->disableOriginalConstructor()
            ->getMock();

        // should not rasterize itself or its children
        $rast->expects($this->never())->method($this->anything());
        $mockChild->expects($this->never())->method('rasterize');
        $obj->rasterize($rast);
    }
}
