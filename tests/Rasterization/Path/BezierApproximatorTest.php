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
        $p0 = [20, 10];
        $p1 = [-25, -15];
        $p2 = [30, 20];
        $result = $bezier->quadratic($p0, $p1, $p2);

        $n = count($result);
        $this->assertEqualsWithDelta(20, $result[0][0], 1);
        $this->assertEqualsWithDelta(10, $result[0][1], 1);
        $this->assertEqualsWithDelta(30, $result[$n - 1][0], 1);
        $this->assertEqualsWithDelta(20, $result[$n - 1][1], 1);
    }

    public function testCubic()
    {
        $bezier = new BezierApproximator();
        $p0 = [20, 10];
        $p1 = [-15, -15];
        $p2 = [-25, 10];
        $p3 = [30, 20];
        $result = $bezier->cubic($p0, $p1, $p2, $p3);

        $n = count($result);
        $this->assertEqualsWithDelta(20, $result[0][0], 1);
        $this->assertEqualsWithDelta(10, $result[0][1], 1);
        $this->assertEqualsWithDelta(30, $result[$n - 1][0], 1);
        $this->assertEqualsWithDelta(20, $result[$n - 1][1], 1);
    }
}
