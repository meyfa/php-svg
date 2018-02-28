<?php

namespace SVG;

use SVG\Rasterization\Path\SVGPathApproximator;

/**
 * @SuppressWarnings(PHPMD)
 */
class SVGPathApproximatorTest extends \PHPUnit\Framework\TestCase
{
    public function testApproximate()
    {
        $approx = new SVGPathApproximator();
        $cmds = [
            ['id' => 'M', 'args' => [10, 20]],
            ['id' => 'm', 'args' => [10, 20]],
            ['id' => 'l', 'args' => [40, 20]],
            ['id' => 'Z', 'args' => []],
        ];
        $result = $approx->approximate($cmds);

        $this->assertSame(10, $result[0][0][0]);
        $this->assertSame(20, $result[0][0][1]);
        $this->assertSame(20, $result[1][0][0]);
        $this->assertSame(40, $result[1][0][1]);
        $this->assertSame(60, $result[1][1][0]);
        $this->assertSame(60, $result[1][1][1]);
        $this->assertSame(20, $result[1][2][0]);
        $this->assertSame(40, $result[1][2][1]);
    }
}
