<?php

namespace SVG;

use SVG\Rasterization\Path\ArcApproximator;

/**
 * @covers SVG\Rasterization\Path\ArcApproximator
 *
 * @SuppressWarnings(PHPMD)
 */
class ArcApproximatorTest extends \PHPUnit\Framework\TestCase
{
    public function testApproximate()
    {
        $approx = new ArcApproximator();
        $p0 = [10.5, 10.5];
        $p1 = [10.5, 10.5];
        $fa = false;
        $fs = false;
        $rx = 10;
        $ry = 10;
        $xa = 2;
        $result = $approx->approximate($p0, $p1, $fa, $fs, $rx, $ry, $xa);

        $this->assertIsArray($result);
        $this->assertCount(0, $result);
    }

    public function testApproximateWithXaIsLessThanZero()
    {
        $approx = new ArcApproximator();
        $p0 = [10.5, 10.5];
        $p1 = [10.5, 10.5];
        $fa = false;
        $fs = false;
        $rx = 10;
        $ry = 10;
        $xa = -2;
        $result = $approx->approximate($p0, $p1, $fa, $fs, $rx, $ry, $xa);

        $this->assertIsArray($result);
        $this->assertCount(0, $result);
    }

    public function testApproximateWithRxAndRyAreZero()
    {
        $approx = new ArcApproximator();
        $p0 = [10.5, 10.5];
        $p1 = [10.6, 10.6];
        $fa = false;
        $fs = false;
        $rx = 0;
        $ry = 0;
        $xa = 2;
        $result = $approx->approximate($p0, $p1, $fa, $fs, $rx, $ry, $xa);

        $this->assertIsArray($result);
        $this->assertSame(10.5, $result[0][0]);
        $this->assertSame(10.5, $result[0][1]);
        $this->assertSame(10.6, $result[1][0]);
        $this->assertSame(10.6, $result[1][1]);
    }

    public function testApproximateWithRxAndRyAreNotZero()
    {
        $approx = new ArcApproximator();
        $p0 = [10.5, 10.5];
        $p1 = [10.6, 10.6];
        $fa = false;
        $fs = false;
        $rx = 1;
        $ry = 1;
        $xa = 2;
        $result = $approx->approximate($p0, $p1, $fa, $fs, $rx, $ry, $xa);

        $this->assertIsArray($result);
        $this->assertEqualsWithDelta(10.5, $result[0][0], 10e-12);
        $this->assertEqualsWithDelta(10.5, $result[0][1], 10e-12);
        $this->assertEqualsWithDelta(10.55, $result[1][0], 0.02);
        $this->assertEqualsWithDelta(10.55, $result[1][1], 0.02);
        $this->assertEqualsWithDelta(10.6, $result[2][0], 10e-12);
        $this->assertEqualsWithDelta(10.6, $result[2][1], 10e-12);
    }

    public function testApproximateFlags()
    {
        $approx = new ArcApproximator();
        $p0 = [10, 10];
        $p1 = [20, 10];
        $rx = 10;
        $ry = 10;
        $xa = 0;

        $fa = false;
        $fs = false;
        $result = $approx->approximate($p0, $p1, $fa, $fs, $rx, $ry, $xa);
        $this->assertIsArray($result);
        // test some point roughly in the middle
        $this->assertEqualsWithDelta(15, $result[count($result) / 2][0], 0.5);
        $this->assertEqualsWithDelta(11.35, $result[count($result) / 2][1], 0.5);

        $fa = true;
        $fs = false;
        $result = $approx->approximate($p0, $p1, $fa, $fs, $rx, $ry, $xa);
        $this->assertIsArray($result);
        $this->assertEqualsWithDelta(15, $result[count($result) / 2][0], 0.5);
        $this->assertEqualsWithDelta(28.65, $result[count($result) / 2][1], 0.5);

        $fa = false;
        $fs = true;
        $result = $approx->approximate($p0, $p1, $fa, $fs, $rx, $ry, $xa);
        $this->assertIsArray($result);
        $this->assertEqualsWithDelta(15, $result[count($result) / 2][0], 0.5);
        $this->assertEqualsWithDelta(8.65, $result[count($result) / 2][1], 0.5);

        $fa = true;
        $fs = true;
        $result = $approx->approximate($p0, $p1, $fa, $fs, $rx, $ry, $xa);
        $this->assertIsArray($result);
        $this->assertEqualsWithDelta(15, $result[count($result) / 2][0], 0.5);
        $this->assertEqualsWithDelta(-8.65, $result[count($result) / 2][1], 0.5);
    }

    public function testApproximateRadiusScaling()
    {
        $approx = new ArcApproximator();
        $p0 = [10, 10];
        $p1 = [20, 10];
        $fa = false;
        $fs = false;
        $rx = 1;
        $ry = 1;
        $xa = 0;
        $result = $approx->approximate($p0, $p1, $fa, $fs, $rx, $ry, $xa);

        $this->assertIsArray($result);

        $n = count($result);
        // expect ellipse to be scaled up 5x to meet start/end points
        $this->assertEqualsWithDelta(10, $result[0][0], 0.1);
        $this->assertEqualsWithDelta(10, $result[0][1], 0.1);
        $this->assertEqualsWithDelta(20, $result[$n - 1][0], 0.1);
        $this->assertEqualsWithDelta(10, $result[$n - 1][1], 0.1);
        // test some point roughly in the middle
        $this->assertEqualsWithDelta(15, $result[$n / 2][0], 1);
        $this->assertEqualsWithDelta(15, $result[$n / 2][1], 1);
    }
}
