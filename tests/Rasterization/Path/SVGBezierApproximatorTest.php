<?php

namespace SVG;

use SVG\Rasterization\Path\SVGBezierApproximator;

/**
 * @SuppressWarnings(PHPMD)
 */
class SVGBezierApproximatorTest extends \PHPUnit\Framework\TestCase
{
    public function testQuadratic()
    {
        $svgBezier = new SVGBezierApproximator();
        $p0 = array(20, 10);
        $p1 = array(-25, -15);
        $p2 = array(30, 20);
        $result = $svgBezier->quadratic($p0, $p1, $p2);

        $n = count($result);
        $this->assertEquals(20, $result[0][0], '', 1);
        $this->assertEquals(10, $result[0][1], '', 1);
        $this->assertEquals(30, $result[$n - 1][0], '', 1);
        $this->assertEquals(20, $result[$n - 1][1], '', 1);
    }

    public function testCubic()
    {
        $svgBezier = new SVGBezierApproximator();
        $p0 = array(20, 10);
        $p1 = array(-15, -15);
        $p2 = array(-25, 10);
        $p3 = array(30, 20);
        $result = $svgBezier->cubic($p0, $p1, $p2, $p3);

        $n = count($result);
        $this->assertEquals(20, $result[0][0], '', 1);
        $this->assertEquals(10, $result[0][1], '', 1);
        $this->assertEquals(30, $result[$n - 1][0], '', 1);
        $this->assertEquals(20, $result[$n - 1][1], '', 1);
    }
}
