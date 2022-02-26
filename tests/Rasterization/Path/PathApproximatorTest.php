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
    // general tests

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
                array(60, 200),
                array(180, 300),
                array(60, 200),
            ),
        ), $result);
    }

    // tests more specific to each path command

    public function testMoveTo()
    {
        // https://www.w3.org/TR/SVG/paths.html#PathDataMovetoCommands
        // "A path data segment (if there is one) must begin with a "moveto" command."
        // If there is no moveto command at the beginning, the approximation should be empty.
        $approx = new PathApproximator(Transform::identity());
        $approx->approximate(array(
            array('id' => 'l', 'args' => array(-5, -7)),
            array('id' => 'M', 'args' => array(10, 20)),
            array('id' => 'L', 'args' => array(37, 41)),
        ));
        $this->assertSame(array(), $approx->getSubpaths());

        // MoveTo should not generate points on its own
        $approx = new PathApproximator(Transform::identity());
        $approx->approximate(array(
            array('id' => 'M', 'args' => array(10, 20)),
            array('id' => 'M', 'args' => array(20, 30)),
            array('id' => 'm', 'args' => array(30, -60)),
            array('id' => 'm', 'args' => array(-40, -100)),
        ));
        $this->assertSame(array(), $approx->getSubpaths());
    }

    public function testLineTo()
    {
        $approx = new PathApproximator(Transform::identity());
        $approx->approximate(array(
            array('id' => 'M', 'args' => array(10, 20)),
            array('id' => 'm', 'args' => array(30, 50)),
            array('id' => 'l', 'args' => array(17, 19)),
            array('id' => 'L', 'args' => array(37, 41)),
            array('id' => 'm', 'args' => array(-100, -300)),
            array('id' => 'l', 'args' => array(19, 23)),
        ));
        $this->assertSame(array(
            array(
                array(40, 70),
                array(57, 89),
                array(37, 41),
            ),
            array(
                array(-63, -259),
                array(-44, -236),
            ),
        ), $approx->getSubpaths());

        // with transform
        $transform = Transform::identity();
        $transform->translate(-40, 90);
        $transform->scale(3, 5);
        $approx = new PathApproximator($transform);
        $approx->approximate(array(
            array('id' => 'M', 'args' => array(10, 20)),
            array('id' => 'm', 'args' => array(30, 50)),
            array('id' => 'l', 'args' => array(17, 19)),
            array('id' => 'L', 'args' => array(37, 41)),
            array('id' => 'm', 'args' => array(-100, -300)),
            array('id' => 'l', 'args' => array(19, 23)),
        ));
        $this->assertSame(array(
            array(
                array(40 * 3 - 40, 70 * 5 + 90),
                array(57 * 3 - 40, 89 * 5 + 90),
                array(37 * 3 - 40, 41 * 5 + 90),
            ),
            array(
                array(-63 * 3 - 40, -259 * 5 + 90),
                array(-44 * 3 - 40, -236 * 5 + 90),
            ),
        ), $approx->getSubpaths());
    }

    public function testLineToHorizontal()
    {
        $approx = new PathApproximator(Transform::identity());
        $approx->approximate(array(
            array('id' => 'M', 'args' => array(10, 20)),
            array('id' => 'h', 'args' => array(170)),
            array('id' => 'H', 'args' => array(370)),
        ));
        $this->assertSame(array(
            array(
                array(10, 20),
                array(180, 20),
                array(370, 20),
            ),
        ), $approx->getSubpaths());

        // with transform
        $transform = Transform::identity();
        $transform->translate(-40, 90);
        $transform->scale(3, 5);
        $approx = new PathApproximator($transform);
        $approx->approximate(array(
            array('id' => 'M', 'args' => array(10, 20)),
            array('id' => 'h', 'args' => array(170)),
            array('id' => 'H', 'args' => array(370)),
        ));
        $this->assertSame(array(
            array(
                array(10 * 3 - 40, 190),
                array(180 * 3 - 40, 190),
                array(370 * 3 - 40, 190),
            ),
        ), $approx->getSubpaths());
    }

    public function testLineToVertical()
    {
        $approx = new PathApproximator(Transform::identity());
        $approx->approximate(array(
            array('id' => 'M', 'args' => array(10, 20)),
            array('id' => 'v', 'args' => array(170)),
            array('id' => 'V', 'args' => array(370)),
        ));
        $this->assertSame(array(
            array(
                array(10, 20),
                array(10, 190),
                array(10, 370),
            ),
        ), $approx->getSubpaths());

        // with transform
        $transform = Transform::identity();
        $transform->translate(-40, 90);
        $transform->scale(3, 5);
        $approx = new PathApproximator($transform);
        $approx->approximate(array(
            array('id' => 'M', 'args' => array(10, 20)),
            array('id' => 'v', 'args' => array(170)),
            array('id' => 'V', 'args' => array(370)),
        ));
        $this->assertSame(array(
            array(
                array(-10, 20 * 5 + 90),
                array(-10, 190 * 5 + 90),
                array(-10, 370 * 5 + 90),
            ),
        ), $approx->getSubpaths());
    }

    public function testClosePath()
    {
        // try with both 'z' and 'Z', which should behave the same
        foreach (array('z', 'Z') as $closeCommand) {
            $approx = new PathApproximator(Transform::identity());
            $approx->approximate(array(
                array('id' => 'M', 'args' => array(10, 20)),
                array('id' => 'L', 'args' => array(50, 70)),
                array('id' => $closeCommand, 'args' => array()),
                array('id' => 'l', 'args' => array(13, 17)),
            ));
            $this->assertSame(array(
                array(
                    array(10, 20),
                    array(50, 70),
                    array(10, 20),
                ),
                array(
                    array(10, 20),
                    array(23, 37),
                ),
            ), $approx->getSubpaths());

            // https://www.w3.org/TR/SVG/paths.html#PathDataClosePathCommand
            // "This path segment may be of zero length."
            $approx = new PathApproximator(Transform::identity());
            $approx->approximate(array(
                array('id' => 'M', 'args' => array(10, 20)),
                array('id' => $closeCommand, 'args' => array()),
                array('id' => 'M', 'args' => array(30, 50)),
                array('id' => 'L', 'args' => array(60, 100)),
                array('id' => 'L', 'args' => array(30, 50)),
                array('id' => $closeCommand, 'args' => array()),
                array('id' => $closeCommand, 'args' => array()),
            ));
            $this->assertSame(array(
                array(
                    array(10, 20),
                    array(10, 20),
                ),
                array(
                    array(30, 50),
                    array(60, 100),
                    array(30, 50),
                    array(30, 50),
                ),
                array(
                    array(30, 50),
                    array(30, 50),
                ),
            ), $approx->getSubpaths());
        }
    }
}
