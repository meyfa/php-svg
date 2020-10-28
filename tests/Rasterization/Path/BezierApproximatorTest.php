<?php

namespace SVG;

use SVG\Rasterization\Path\BezierApproximator;

/**
 * @covers \SVG\Rasterization\Path\BezierApproximator
 *
 * @SuppressWarnings(PHPMD)
 */
class BezierApproximatorTest extends \PHPUnit\Framework\TestCase
{
    public function testQuadratic()
    {
        $bezier = new BezierApproximator();
        $p0 = array(20, 10);
        $p1 = array(-25, -15);
        $p2 = array(30, 20);
        $result = $bezier->quadratic($p0, $p1, $p2);

        $n = count($result);
        $this->assertEquals(20, $result[0][0], '', 1);
        $this->assertEquals(10, $result[0][1], '', 1);
        $this->assertEquals(30, $result[$n - 1][0], '', 1);
        $this->assertEquals(20, $result[$n - 1][1], '', 1);
    }

    public function testCubic()
    {
        $bezier = new BezierApproximator();
        $p0 = array(20, 10);
        $p1 = array(-15, -15);
        $p2 = array(-25, 10);
        $p3 = array(30, 20);
        $result = $bezier->cubic($p0, $p1, $p2, $p3);

        $n = count($result);
        $this->assertEquals(20, $result[0][0], '', 1);
        $this->assertEquals(10, $result[0][1], '', 1);
        $this->assertEquals(30, $result[$n - 1][0], '', 1);
        $this->assertEquals(20, $result[$n - 1][1], '', 1);
    }
}
