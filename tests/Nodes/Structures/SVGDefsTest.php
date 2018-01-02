<?php

use SVG\Nodes\Structures\SVGDefs;

/**
 * @SuppressWarnings(PHPMD)
 */
 class SVGDefsTest extends PHPUnit_Framework_TestCase
{
    public function test__construct()
    {
        // should not set any attributes by default
        $obj = new SVGDefs();
        $this->assertSame(array(), $obj->getSerializableAttributes());
    }

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
