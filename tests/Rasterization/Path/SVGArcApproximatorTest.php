<?php

namespace SVG;

use SVG\Rasterization\Path\SVGArcApproximator;

/**
 * @SuppressWarnings(PHPMD)
 */
class SVGArcApproximatorTest extends \PHPUnit\Framework\TestCase
{
    // THE TESTS IN THIS CLASS DO NOT ADHERE TO THE STANDARD LAYOUT
    // OF TESTING ONE CLASS METHOD PER TEST METHOD
    // BECAUSE THE CLASS UNDER TEST IS A SINGLE-FEATURE CLASS

    public function testApproximate()
    {
        $approx = new SVGArcApproximator();
        $p0 = array(10.5, 10.5);
        $p1 = array(10.5, 10.5);
        $fa = false;
        $fs = false;
        $rx = 10;
        $ry = 10;
        $xa = 2;
        $result = $approx->approximate($p0, $p1, $fa, $fs, $rx, $ry, $xa);

        $this->assertInternalType('array', $result);
        $this->assertCount(0, $result);
    }

    public function testApproximateWithXaIsLessThanZero()
    {
        $approx = new SVGArcApproximator();
        $p0 = array(10.5, 10.5);
        $p1 = array(10.5, 10.5);
        $fa = false;
        $fs = false;
        $rx = 10;
        $ry = 10;
        $xa = -2;
        $result = $approx->approximate($p0, $p1, $fa, $fs, $rx, $ry, $xa);

        $this->assertInternalType('array', $result);
        $this->assertCount(0, $result);
    }

    public function testApproximateWithRxAndRyAreZero()
    {
        $approx = new SVGArcApproximator();
        $p0 = array(10.5, 10.5);
        $p1 = array(10.6, 10.6);
        $fa = false;
        $fs = false;
        $rx = 0;
        $ry = 0;
        $xa = 2;
        $result = $approx->approximate($p0, $p1, $fa, $fs, $rx, $ry, $xa);

        $this->assertInternalType('array', $result);
        $this->assertSame(10.5, $result[0][0]);
        $this->assertSame(10.5, $result[0][1]);
        $this->assertSame(10.6, $result[1][0]);
        $this->assertSame(10.6, $result[1][1]);
    }

    public function testApproximateWithRxAndRyAreNotZero()
    {
        $approx = new SVGArcApproximator();
        $p0 = array(10.5, 10.5);
        $p1 = array(10.6, 10.6);
        $fa = false;
        $fs = false;
        $rx = 1;
        $ry = 1;
        $xa = 2;
        $result = $approx->approximate($p0, $p1, $fa, $fs, $rx, $ry, $xa);

        $this->assertInternalType('array', $result);
        $this->assertSame(10.5, $result[0][0]);
        $this->assertSame(10.5, $result[0][1]);
        $this->assertEquals(10.55, $result[1][0], '', 0.02);
        $this->assertEquals(10.55, $result[1][1], '', 0.02);
        $this->assertSame(10.6, $result[2][0]);
        $this->assertSame(10.6, $result[2][1]);
    }

    public function testApproximateLargeArcFlag()
    {
        $approx = new SVGArcApproximator();
        $p0 = array(10, 10);
        $p1 = array(20, 10);
        $fa = true;
        $fs = false;
        $rx = 10;
        $ry = 10;
        $xa = 0;

        $result = $approx->approximate($p0, $p1, $fa, $fs, $rx, $ry, $xa);
        // test some point roughly in the middle
        $this->assertTrue($result[count($result) / 2][1] > 27, 'arc does not descend enough');

        $fa = false;
        $result = $approx->approximate($p0, $p1, $fa, $fs, $rx, $ry, $xa);
        $this->assertTrue($result[count($result) / 2][1] < 12, 'arc descends too far');
    }
}
