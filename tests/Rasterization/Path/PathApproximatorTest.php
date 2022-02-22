<?php

namespace SVG;

use SVG\Rasterization\Path\PathApproximator;

/**
 * @covers \SVG\Rasterization\Path\PathApproximator
 *
 * @SuppressWarnings(PHPMD)
 */
class PathApproximatorTest extends \PHPUnit\Framework\TestCase
{
    public function testApproximate()
    {
        $approx = new PathApproximator();
        $cmds = array(
            array('id' => 'M', 'args' => array(10, 20)),
            array('id' => 'm', 'args' => array(10, 20)),
            array('id' => 'l', 'args' => array(40, 20)),
            array('id' => 'Z', 'args' => array()),
        );
        $approx->approximate($cmds);
        $result = $approx->getSubpaths();

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
