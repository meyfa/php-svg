<?php

namespace SVG\Tests\Nodes\Structures;

use PHPUnit\Framework\TestCase;
use SVG\Nodes\Structures\SVGScript;
use SVG\Rasterization\SVGRasterizer;

/**
 * @coversDefaultClass \SVG\Nodes\Structures\SVGScript
 *
 * @SuppressWarnings(PHPMD)
 */
class SVGScriptTest extends TestCase
{
    /**
     * @covers ::__construct
     */
    public function test__construct(): void
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
    public function testRasterize(): void
    {
        $obj = new SVGScript();

        $rast = $this->getMockBuilder(SVGRasterizer::class)
            ->disableOriginalConstructor()
            ->getMock();

        // should not manipulate anything
        $rast->expects($this->never())->method($this->anything());
        $obj->rasterize($rast);
    }
}
