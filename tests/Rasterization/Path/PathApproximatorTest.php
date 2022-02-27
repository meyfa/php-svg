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

    public function testApproximateStopsAtInvalidCommand()
    {
        $approx = new PathApproximator(Transform::identity());
        $cmds = array(
            array('id' => 'M', 'args' => array(10, 20)),
            array('id' => 'l', 'args' => array(40, 20)),
            array('id' => 'x', 'args' => array()),
            array('id' => 'l', 'args' => array(0, 10)),
            array('id' => 'Z', 'args' => array()),
        );
        $approx->approximate($cmds);
        $result = $approx->getSubpaths();

        $this->assertSame(array(
            array(
                array(10, 20),
                array(50, 40),
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

    public function testCurveToCubic()
    {
        // Simple test: draw cubic curves representing straight lines
        $approx = new PathApproximator(Transform::identity());
        $approx->approximate(array(
            array('id' => 'M', 'args' => array(10, 20)),
            array('id' => 'C', 'args' => array(12, 20, 18, 20, 20, 20)),
            array('id' => 'c', 'args' => array(0, 2, 0, 8, 0, 10)),
        ));
        $subpaths = $approx->getSubpaths();
        $this->assertCount(1, $subpaths);
        $this->assertGreaterThan(10, count($subpaths[0]));
        // check end points
        $this->assertEquals(array(10, 20), $subpaths[0][0]);
        $this->assertEquals(array(20, 30), $subpaths[0][count($subpaths[0]) - 1]);
        // find the index of the corner (end point of the first CurveToCubic command)
        for ($corner = 0; $corner < count($subpaths[0]) && $subpaths[0][$corner] != array(20, 20);) {
            ++$corner;
        }
        // expect it to be in the middle
        $this->assertGreaterThan(0.4 * count($subpaths[0]), $corner);
        $this->assertLessThan(0.6 * count($subpaths[0]), $corner);
        // expect everything before to be a straight horizontal line
        for ($i = 1; $i <= $corner; ++$i) {
            list($xPrev, $yPrev) = $subpaths[0][$i - 1];
            list($x, $y) = $subpaths[0][$i];
            $this->assertGreaterThanOrEqual($xPrev, $x);
            $this->assertLessThanOrEqual(20, $x);
            $this->assertEquals($yPrev, $y);
        }
        // expect everything after to be a straight vertical line
        for ($i = $corner + 1; $i < count($subpaths[0]); ++$i) {
            list($xPrev, $yPrev) = $subpaths[0][$i - 1];
            list($x, $y) = $subpaths[0][$i];
            $this->assertEquals($xPrev, $x);
            $this->assertGreaterThanOrEqual($yPrev, $y);
            $this->assertLessThanOrEqual(30, $y);
        }

        // Ensure that with larger transform scale, the number of points increases (but not by too much)
        $transform = Transform::identity();
        $transform->scale(4, 4);
        $approx = new PathApproximator($transform);
        $approx->approximate(array(
            array('id' => 'M', 'args' => array(10, 20)),
            array('id' => 'C', 'args' => array(12, 20, 18, 20, 20, 20)),
            array('id' => 'c', 'args' => array(0, 2, 0, 8, 0, 10)),
        ));
        $subpaths2 = $approx->getSubpaths();
        $this->assertGreaterThan(3.5 * count($subpaths[0]), count($subpaths2[0]));
        $this->assertLessThan(4.5 * count($subpaths[0]), count($subpaths2[0]));
    }

    public function testCurveToCubicSmooth()
    {
        // without preceding CurveToCubic
        $approx = new PathApproximator(Transform::identity());
        $approx->approximate(array(
            array('id' => 'M', 'args' => array(10, 20)),
            array('id' => 'S', 'args' => array(15, 30, 20, 20)),
        ));
        $subpaths = $approx->getSubpaths();
        $this->assertCount(1, $subpaths);
        $this->assertGreaterThan(10, count($subpaths[0]));
        // check end points
        $this->assertEquals(array(10, 20), $subpaths[0][0]);
        $this->assertEquals(array(20, 20), $subpaths[0][count($subpaths[0]) - 1]);
        // check center (the curve should bend downwards about halfway to the only control point)
        list($x, $y) = $subpaths[0][ceil(count($subpaths[0]) / 2)];
        $this->assertEquals(15, $x, null, 1.0);
        $this->assertEquals(25, $y, null, 1.0);

        // with preceding CurveToCubic (test reflection of control point)
        // this also checks the relative variant
        $approx = new PathApproximator(Transform::identity());
        $approx->approximate(array(
            array('id' => 'M', 'args' => array(10, 20)),
            array('id' => 'C', 'args' => array(14, 30, 16, 30, 20, 20)),
            array('id' => 's', 'args' => array(6, -10, 10, 0)),
        ));
        $subpaths = $approx->getSubpaths();
        $this->assertCount(1, $subpaths);
        $this->assertGreaterThan(10, count($subpaths[0]));
        // check end points
        $this->assertEquals(array(10, 20), $subpaths[0][0]);
        $this->assertEquals(array(30, 20), $subpaths[0][count($subpaths[0]) - 1]);
        // The curve should first bend down due to 'C', then bend up due to the reflected 's'.
        // - check at 1/4 from beginning
        list($x, $y) = $subpaths[0][ceil(count($subpaths[0]) * 0.25)];
        $this->assertEquals(15, $x, null, 1.0);
        $this->assertEquals(27.5, $y, null, 1.0);
        // - check at 1/2 from beginning
        list($x, $y) = $subpaths[0][ceil(count($subpaths[0]) * 0.5)];
        $this->assertEquals(20, $x, null, 1.0);
        $this->assertEquals(20, $y, null, 1.0);
        // - check at 3/4 from beginning
        list($x, $y) = $subpaths[0][ceil(count($subpaths[0]) * 0.75)];
        $this->assertEquals(25, $x, null, 1.0);
        $this->assertEquals(12.5, $y, null, 1.0);
    }

    public function testCurveToQuadratic()
    {
        // Simple test: draw quadratic curves representing straight lines
        $approx = new PathApproximator(Transform::identity());
        $approx->approximate(array(
            array('id' => 'M', 'args' => array(10, 20)),
            array('id' => 'Q', 'args' => array(15, 20, 20, 20)),
            array('id' => 'q', 'args' => array(0, 5, 0, 10)),
        ));
        $subpaths = $approx->getSubpaths();
        $this->assertCount(1, $subpaths);
        $this->assertGreaterThan(10, count($subpaths[0]));
        // check end points
        $this->assertEquals(array(10, 20), $subpaths[0][0]);
        $this->assertEquals(array(20, 30), $subpaths[0][count($subpaths[0]) - 1]);
        // find the index of the corner (end point of the first CurveToQuadratic command)
        for ($corner = 0; $corner < count($subpaths[0]) && $subpaths[0][$corner] != array(20, 20);) {
            ++$corner;
        }
        // expect it to be in the middle
        $this->assertGreaterThan(0.4 * count($subpaths[0]), $corner);
        $this->assertLessThan(0.6 * count($subpaths[0]), $corner);
        // expect everything before to be a straight horizontal line
        for ($i = 1; $i <= $corner; ++$i) {
            list($xPrev, $yPrev) = $subpaths[0][$i - 1];
            list($x, $y) = $subpaths[0][$i];
            $this->assertGreaterThanOrEqual($xPrev, $x);
            $this->assertLessThanOrEqual(20, $x);
            $this->assertEquals($yPrev, $y);
        }
        // expect everything after to be a straight vertical line
        for ($i = $corner + 1; $i < count($subpaths[0]); ++$i) {
            list($xPrev, $yPrev) = $subpaths[0][$i - 1];
            list($x, $y) = $subpaths[0][$i];
            $this->assertEquals($xPrev, $x);
            $this->assertGreaterThanOrEqual($yPrev, $y);
            $this->assertLessThanOrEqual(30, $y);
        }

        // Ensure that with larger transform scale, the number of points increases (but not by too much)
        $transform = Transform::identity();
        $transform->scale(4, 4);
        $approx = new PathApproximator($transform);
        $approx->approximate(array(
            array('id' => 'M', 'args' => array(10, 20)),
            array('id' => 'Q', 'args' => array(15, 20, 20, 20)),
            array('id' => 'q', 'args' => array(0, 5, 0, 10)),
        ));
        $subpaths2 = $approx->getSubpaths();
        $this->assertGreaterThan(3.5 * count($subpaths[0]), count($subpaths2[0]));
        $this->assertLessThan(4.5 * count($subpaths[0]), count($subpaths2[0]));
    }

    public function testCurveToQuadraticSmooth()
    {
        // without preceding CurveToQuadratic
        $approx = new PathApproximator(Transform::identity());
        $approx->approximate(array(
            array('id' => 'M', 'args' => array(10, 20)),
            array('id' => 'T', 'args' => array(20, 20)),
        ));
        $subpaths = $approx->getSubpaths();
        $this->assertCount(1, $subpaths);
        $this->assertGreaterThan(10, count($subpaths[0]));
        // check end points
        $this->assertEquals(array(10, 20), $subpaths[0][0]);
        $this->assertEquals(array(20, 20), $subpaths[0][count($subpaths[0]) - 1]);
        // check center (the curve should represent a flat horizontal line)
        list($x, $y) = $subpaths[0][ceil(count($subpaths[0]) / 2)];
        $this->assertEquals(15, $x, null, 1.0);
        $this->assertEquals(20, $y, null, 1.0);

        // with preceding CurveToQuadratic (test reflection of control point)
        // this also checks the relative variant
        $approx = new PathApproximator(Transform::identity());
        $approx->approximate(array(
            array('id' => 'M', 'args' => array(10, 20)),
            array('id' => 'Q', 'args' => array(15, 30, 20, 20)),
            array('id' => 't', 'args' => array(10, 0)),
        ));
        $subpaths = $approx->getSubpaths();
        $this->assertCount(1, $subpaths);
        $this->assertGreaterThan(10, count($subpaths[0]));
        // check end points
        $this->assertEquals(array(10, 20), $subpaths[0][0]);
        $this->assertEquals(array(30, 20), $subpaths[0][count($subpaths[0]) - 1]);
        // The curve should first bend down due to 'Q', then bend up due to the reflected 't'.
        // - check at 1/4 from beginning
        list($x, $y) = $subpaths[0][ceil(count($subpaths[0]) * 0.25)];
        $this->assertEquals(15, $x, null, 1.0);
        $this->assertEquals(25, $y, null, 1.0);
        // - check at 1/2 from beginning
        list($x, $y) = $subpaths[0][ceil(count($subpaths[0]) * 0.5)];
        $this->assertEquals(20, $x, null, 1.0);
        $this->assertEquals(20, $y, null, 1.0);
        // - check at 3/4 from beginning
        list($x, $y) = $subpaths[0][ceil(count($subpaths[0]) * 0.75)];
        $this->assertEquals(25, $x, null, 1.0);
        $this->assertEquals(15, $y, null, 1.0);
    }

    public function testArcTo()
    {
        // Run the following test once for the absolute command and once for the relative command.
        // The coordinates are chosen so that the output should be equal.
        $commands = array(
            array('id' => 'A', 'args' => array(5, 10, 0, 1, 0, 20, 20)),
            array('id' => 'a', 'args' => array(5, 10, 0, 1, 0, 10, 0)),
        );
        foreach ($commands as $command) {
            $approx = new PathApproximator(Transform::identity());
            $approx->approximate(array(
                array('id' => 'M', 'args' => array(10, 20)),
                $command,
            ));
            $subpaths = $approx->getSubpaths();
            $this->assertCount(1, $subpaths);
            $this->assertGreaterThan(10, count($subpaths[0]));
            // check end points
            $this->assertEquals(array(10, 20), $subpaths[0][0]);
            $this->assertEquals(array(20, 20), $subpaths[0][count($subpaths[0]) - 1]);
            // points in between should have x >= 10 && x <= 20 and y >= 20, y <= 30
            foreach ($subpaths[0] as $point) {
                list($x, $y) = $point;
                $this->assertGreaterThanOrEqual(10, $x);
                $this->assertLessThanOrEqual(20, $x);
                $this->assertGreaterThanOrEqual(20, $y);
                $this->assertLessThanOrEqual(30, $y);
            }
            // midpoint should roughly be at [15, 30]
            list($x, $y) = $subpaths[0][ceil(count($subpaths[0]) / 2)];
            $this->assertEquals(15, $x, null, 0.5);
            $this->assertEquals(30, $y, null, 0.5);
        }

        // Ensure that with larger transform scale, the number of points increases (but not by too much)
        $transform = Transform::identity();
        $transform->scale(4, 4);
        $approx = new PathApproximator($transform);
        $approx->approximate(array(
            array('id' => 'M', 'args' => array(10, 20)),
            array('id' => 'A', 'args' => array(5, 10, 0, 1, 0, 20, 20)),
        ));
        $subpaths2 = $approx->getSubpaths();
        $this->assertGreaterThan(3.5 * count($subpaths[0]), count($subpaths2[0]));
        $this->assertLessThan(4.5 * count($subpaths[0]), count($subpaths2[0]));
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
