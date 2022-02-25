<?php

namespace SVG;

use SVG\Rasterization\Path\PathApproximator;
use SVG\Rasterization\Transform\Transform;

/**
 * @covers \SVG\Rasterization\Path\PathApproximator
 *
 * @SuppressWarnings(PHPMD)
 */
class PathApproximatorTest extends \PHPUnit\Framework\TestCase
{
    public function testApproximate()
    {
        $approx = new PathApproximator(Transform::identity());
        $cmds = array(
            array('id' => 'M', 'args' => array(10, 20)),
            array('id' => 'm', 'args' => array(10, 20)),
            array('id' => 'l', 'args' => array(40, 20)),
            array('id' => 'Z', 'args' => array()),
        );
        $approx->approximate($cmds);
        $result = $approx->getSubpaths();

        $this->assertSame(array(
            array(
                array(10, 20),
            ),
            array(
                array(20, 40),
                array(60, 60),
                array(20, 40),
            ),
        ), $result);
    }

    public function testApproximateWithTransform()
    {
        $transform = Transform::identity();
        $transform->scale(3, 5);

        $approx = new PathApproximator($transform);
        $cmds = array(
            array('id' => 'M', 'args' => array(10, 20)),
            array('id' => 'm', 'args' => array(10, 20)),
            array('id' => 'l', 'args' => array(40, 20)),
            array('id' => 'Z', 'args' => array()),
        );
        $approx->approximate($cmds);
        $result = $approx->getSubpaths();

        $this->assertSame(array(
            array(
                array(30, 100),
            ),
            array(
                array(60, 200),
                array(180, 300),
                array(60, 200),
            ),
        ), $result);
    }
}
