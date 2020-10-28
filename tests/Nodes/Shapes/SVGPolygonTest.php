<?php

namespace SVG;

use SVG\Nodes\Shapes\SVGPolygon;

/**
 * @coversDefaultClass \SVG\Nodes\Shapes\SVGPolygon
 * @covers ::<!public>
 *
 * @SuppressWarnings(PHPMD)
 */
class SVGPolygonTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers ::__construct
     */
    public function test__construct()
    {
        // should set empty points by default
        $obj = new SVGPolygon();
        $this->assertSame(array(), $obj->getPoints());

        // should set points when provided
        $points = array(
            array(42.5, 42.5),
            array(37, 37),
        );
        $obj = new SVGPolygon($points);
        $this->assertEquals($points, $obj->getPoints());
    }

    /**
     * @covers ::rasterize
     */
    public function testRasterize()
    {
        $points = array(
            array(42.5, 42.5),
            array(37, 37),
        );

        $obj = new SVGPolygon($points);

        $rast = $this->getMockBuilder('\SVG\Rasterization\SVGRasterizer')
            ->disableOriginalConstructor()
            ->getMock();

        // should call image renderer with correct options
        $rast->expects($this->once())->method('render')->with(
            $this->identicalTo('polygon'),
            $this->equalTo(array(
                'open' => false,
                'points' => $points,
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
