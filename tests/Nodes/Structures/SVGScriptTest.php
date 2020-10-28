<?php

namespace SVG;

use SVG\Nodes\Structures\SVGScript;

/**
 * @coversDefaultClass \SVG\Nodes\Structures\SVGScript
 *
 * @SuppressWarnings(PHPMD)
 */
class SVGScriptTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers ::__construct
     */
    public function test__construct()
    {
        // should default to empty CSS, type = text/css
        $obj = new SVGScript();
        $this->assertSame('', $obj->getValue());

        // should allow setting CSS and type
        $obj = new SVGScript('alert()');
        $this->assertSame('alert()', $obj->getValue());
    }

    /**
     * @covers ::rasterize
     */
    public function testRasterize()
    {
        $obj = new SVGScript();

        $rast = $this->getMockBuilder('\SVG\Rasterization\SVGRasterizer')
            ->disableOriginalConstructor()
            ->getMock();

        // should not manipulate anything
        $rast->expects($this->never())->method($this->anything());
        $obj->rasterize($rast);
    }
}
