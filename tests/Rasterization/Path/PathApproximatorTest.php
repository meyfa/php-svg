<?php

namespace SVG\Tests\Rasterization\Path;

use PHPUnit\Framework\TestCase;
use SVG\Rasterization\Path\PathApproximator;
use SVG\Rasterization\Transform\Transform;

/**
 * @covers \SVG\Rasterization\Path\PathApproximator
 *
 * @SuppressWarnings(PHPMD)
 */
class PathApproximatorTest extends TestCase
{
    // general tests

    public function testApproximate(): void
    {
        $approx = new PathApproximator(Transform::identity());
        $cmds = [
            ['id' => 'M', 'args' => [10, 20]],
            ['id' => 'm', 'args' => [10, 20]],
            ['id' => 'l', 'args' => [40, 20]],
            ['id' => 'Z', 'args' => []],
        ];
        $approx->approximate($cmds);
        $result = $approx->getSubpaths();

        $this->assertSame([
            [
                [20.0, 40.0],
                [60.0, 60.0],
                [20.0, 40.0],
            ],
        ], $result);
    }

    public function testApproximateWithTransform(): void
    {
        $transform = Transform::identity();
        $transform->scale(3, 5);

        $approx = new PathApproximator($transform);
        $cmds = [
            ['id' => 'M', 'args' => [10, 20]],
            ['id' => 'm', 'args' => [10, 20]],
            ['id' => 'l', 'args' => [40, 20]],
            ['id' => 'Z', 'args' => []],
        ];
        $approx->approximate($cmds);
        $result = $approx->getSubpaths();

        $this->assertSame([
            [
                [60.0, 200.0],
                [180.0, 300.0],
                [60.0, 200.0],
            ],
        ], $result);
    }

    public function testApproximateStopsAtInvalidCommand(): void
    {
        $approx = new PathApproximator(Transform::identity());
        $cmds = [
            ['id' => 'M', 'args' => [10, 20]],
            ['id' => 'l', 'args' => [40, 20]],
            ['id' => 'x', 'args' => []],
            ['id' => 'l', 'args' => [0, 10]],
            ['id' => 'Z', 'args' => []],
        ];
        $approx->approximate($cmds);
        $result = $approx->getSubpaths();

        $this->assertSame([
            [
                [10.0, 20.0],
                [50.0, 40.0],
            ],
        ], $result);
    }

    // tests more specific to each path command

    public function testMoveTo(): void
    {
        // https://www.w3.org/TR/SVG/paths.html#PathDataMovetoCommands
        // "A path data segment (if there is one) must begin with a "moveto" command."
        // If there is no moveto command at the beginning, the approximation should be empty.
        $approx = new PathApproximator(Transform::identity());
        $approx->approximate([
            ['id' => 'l', 'args' => [-5, -7]],
            ['id' => 'M', 'args' => [10, 20]],
            ['id' => 'L', 'args' => [37, 41]],
        ]);
        $this->assertSame([], $approx->getSubpaths());

        // MoveTo should not generate points on its own
        $approx = new PathApproximator(Transform::identity());
        $approx->approximate([
            ['id' => 'M', 'args' => [10, 20]],
            ['id' => 'M', 'args' => [20, 30]],
            ['id' => 'm', 'args' => [30, -60]],
            ['id' => 'm', 'args' => [-40, -100]],
        ]);
        $this->assertSame([], $approx->getSubpaths());
    }

    public function testLineTo(): void
    {
        $approx = new PathApproximator(Transform::identity());
        $approx->approximate([
            ['id' => 'M', 'args' => [10, 20]],
            ['id' => 'm', 'args' => [30, 50]],
            ['id' => 'l', 'args' => [17, 19]],
            ['id' => 'L', 'args' => [37, 41]],
            ['id' => 'm', 'args' => [-100, -300]],
            ['id' => 'l', 'args' => [19, 23]],
        ]);
        $this->assertSame([
            [
                [40.0, 70.0],
                [57.0, 89.0],
                [37.0, 41.0],
            ],
            [
                [-63.0, -259.0],
                [-44.0, -236.0],
            ],
        ], $approx->getSubpaths());

        // with transform
        $transform = Transform::identity();
        $transform->translate(-40, 90);
        $transform->scale(3, 5);
        $approx = new PathApproximator($transform);
        $approx->approximate([
            ['id' => 'M', 'args' => [10, 20]],
            ['id' => 'm', 'args' => [30, 50]],
            ['id' => 'l', 'args' => [17, 19]],
            ['id' => 'L', 'args' => [37, 41]],
            ['id' => 'm', 'args' => [-100, -300]],
            ['id' => 'l', 'args' => [19, 23]],
        ]);
        $this->assertSame([
            [
                [40.0 * 3 - 40, 70.0 * 5 + 90],
                [57.0 * 3 - 40, 89.0 * 5 + 90],
                [37.0 * 3 - 40, 41.0 * 5 + 90],
            ],
            [
                [-63.0 * 3 - 40, -259.0 * 5 + 90],
                [-44.0 * 3 - 40, -236.0 * 5 + 90],
            ],
        ], $approx->getSubpaths());
    }

    public function testLineToHorizontal(): void
    {
        $approx = new PathApproximator(Transform::identity());
        $approx->approximate([
            ['id' => 'M', 'args' => [10, 20]],
            ['id' => 'h', 'args' => [170]],
            ['id' => 'H', 'args' => [370]],
        ]);
        $this->assertSame([
            [
                [10.0, 20.0],
                [180.0, 20.0],
                [370.0, 20.0],
            ],
        ], $approx->getSubpaths());

        // with transform
        $transform = Transform::identity();
        $transform->translate(-40, 90);
        $transform->scale(3, 5);
        $approx = new PathApproximator($transform);
        $approx->approximate([
            ['id' => 'M', 'args' => [10, 20]],
            ['id' => 'h', 'args' => [170]],
            ['id' => 'H', 'args' => [370]],
        ]);
        $this->assertSame([
            [
                [10.0 * 3 - 40, 190.0],
                [180.0 * 3 - 40, 190.0],
                [370.0 * 3 - 40, 190.0],
            ],
        ], $approx->getSubpaths());
    }

    public function testLineToVertical(): void
    {
        $approx = new PathApproximator(Transform::identity());
        $approx->approximate([
            ['id' => 'M', 'args' => [10, 20]],
            ['id' => 'v', 'args' => [170]],
            ['id' => 'V', 'args' => [370]],
        ]);
        $this->assertSame([
            [
                [10.0, 20.0],
                [10.0, 190.0],
                [10.0, 370.0],
            ],
        ], $approx->getSubpaths());

        // with transform
        $transform = Transform::identity();
        $transform->translate(-40, 90);
        $transform->scale(3, 5);
        $approx = new PathApproximator($transform);
        $approx->approximate([
            ['id' => 'M', 'args' => [10, 20]],
            ['id' => 'v', 'args' => [170]],
            ['id' => 'V', 'args' => [370]],
        ]);
        $this->assertSame([
            [
                [-10.0, 20.0 * 5 + 90],
                [-10.0, 190.0 * 5 + 90],
                [-10.0, 370.0 * 5 + 90],
            ],
        ], $approx->getSubpaths());
    }

    public function testCurveToCubic(): void
    {
        // Simple test: draw cubic curves representing straight lines
        $approx = new PathApproximator(Transform::identity());
        $approx->approximate([
            ['id' => 'M', 'args' => [10, 20]],
            ['id' => 'C', 'args' => [12, 20, 18, 20, 20, 20]],
            ['id' => 'c', 'args' => [0, 2, 0, 8, 0, 10]],
        ]);
        $subpaths = $approx->getSubpaths();
        $this->assertCount(1, $subpaths);
        $this->assertGreaterThan(10, count($subpaths[0]));
        // check end points
        $this->assertEquals([10, 20], $subpaths[0][0]);
        $this->assertEquals([20, 30], $subpaths[0][count($subpaths[0]) - 1]);
        // find the index of the corner (end point of the first CurveToCubic command)
        for ($corner = 0; $corner < count($subpaths[0]) && $subpaths[0][$corner] != [20, 20];) {
            ++$corner;
        }
        // expect it to be in the middle
        $this->assertGreaterThan(0.4 * count($subpaths[0]), $corner);
        $this->assertLessThan(0.6 * count($subpaths[0]), $corner);
        // expect everything before to be a straight horizontal line
        for ($i = 1; $i <= $corner; ++$i) {
            [$xPrev, $yPrev] = $subpaths[0][$i - 1];
            [$x, $y] = $subpaths[0][$i];
            $this->assertGreaterThanOrEqual($xPrev, $x);
            $this->assertLessThanOrEqual(20, $x);
            $this->assertEquals($yPrev, $y);
        }
        // expect everything after to be a straight vertical line
        for ($i = $corner + 1; $i < count($subpaths[0]); ++$i) {
            [$xPrev, $yPrev] = $subpaths[0][$i - 1];
            [$x, $y] = $subpaths[0][$i];
            $this->assertEquals($xPrev, $x);
            $this->assertGreaterThanOrEqual($yPrev, $y);
            $this->assertLessThanOrEqual(30, $y);
        }

        // Ensure that with larger transform scale, the number of points increases (but not by too much)
        $transform = Transform::identity();
        $transform->scale(4, 4);
        $approx = new PathApproximator($transform);
        $approx->approximate([
            ['id' => 'M', 'args' => [10, 20]],
            ['id' => 'C', 'args' => [12, 20, 18, 20, 20, 20]],
            ['id' => 'c', 'args' => [0, 2, 0, 8, 0, 10]],
        ]);
        $subpaths2 = $approx->getSubpaths();
        $this->assertGreaterThan(3.5 * count($subpaths[0]), count($subpaths2[0]));
        $this->assertLessThan(4.5 * count($subpaths[0]), count($subpaths2[0]));
    }

    public function testCurveToCubicSmooth(): void
    {
        // without preceding CurveToCubic
        $approx = new PathApproximator(Transform::identity());
        $approx->approximate([
            ['id' => 'M', 'args' => [10, 20]],
            ['id' => 'S', 'args' => [15, 30, 20, 20]],
        ]);
        $subpaths = $approx->getSubpaths();
        $this->assertCount(1, $subpaths);
        $this->assertGreaterThan(10, count($subpaths[0]));
        // check end points
        $this->assertEquals([10, 20], $subpaths[0][0]);
        $this->assertEquals([20, 20], $subpaths[0][count($subpaths[0]) - 1]);
        // check center (the curve should bend downwards about halfway to the only control point)
        [$x, $y] = $subpaths[0][ceil(count($subpaths[0]) / 2)];
        $this->assertEqualsWithDelta(15, $x, 1.0);
        $this->assertEqualsWithDelta(25, $y, 1.0);

        // with preceding CurveToCubic (test reflection of control point)
        // this also checks the relative variant
        $approx = new PathApproximator(Transform::identity());
        $approx->approximate([
            ['id' => 'M', 'args' => [10, 20]],
            ['id' => 'C', 'args' => [14, 30, 16, 30, 20, 20]],
            ['id' => 's', 'args' => [6, -10, 10, 0]],
        ]);
        $subpaths = $approx->getSubpaths();
        $this->assertCount(1, $subpaths);
        $this->assertGreaterThan(10, count($subpaths[0]));
        // check end points
        $this->assertEquals([10, 20], $subpaths[0][0]);
        $this->assertEquals([30, 20], $subpaths[0][count($subpaths[0]) - 1]);
        // The curve should first bend down due to 'C', then bend up due to the reflected 's'.
        // - check at 1/4 from beginning
        [$x, $y] = $subpaths[0][ceil(count($subpaths[0]) * 0.25)];
        $this->assertEqualsWithDelta(15, $x, 1.0);
        $this->assertEqualsWithDelta(27.5, $y, 1.0);
        // - check at 1/2 from beginning
        [$x, $y] = $subpaths[0][ceil(count($subpaths[0]) * 0.5)];
        $this->assertEqualsWithDelta(20, $x, 1.0);
        $this->assertEqualsWithDelta(20, $y, 1.0);
        // - check at 3/4 from beginning
        [$x, $y] = $subpaths[0][ceil(count($subpaths[0]) * 0.75)];
        $this->assertEqualsWithDelta(25, $x, 1.0);
        $this->assertEqualsWithDelta(12.5, $y, 1.0);
    }

    public function testCurveToQuadratic(): void
    {
        // Simple test: draw quadratic curves representing straight lines
        $approx = new PathApproximator(Transform::identity());
        $approx->approximate([
            ['id' => 'M', 'args' => [10, 20]],
            ['id' => 'Q', 'args' => [15, 20, 20, 20]],
            ['id' => 'q', 'args' => [0, 5, 0, 10]],
        ]);
        $subpaths = $approx->getSubpaths();
        $this->assertCount(1, $subpaths);
        $this->assertGreaterThan(10, count($subpaths[0]));
        // check end points
        $this->assertEquals([10, 20], $subpaths[0][0]);
        $this->assertEquals([20, 30], $subpaths[0][count($subpaths[0]) - 1]);
        // find the index of the corner (end point of the first CurveToQuadratic command)
        for ($corner = 0; $corner < count($subpaths[0]) && $subpaths[0][$corner] != [20, 20];) {
            ++$corner;
        }
        // expect it to be in the middle
        $this->assertGreaterThan(0.4 * count($subpaths[0]), $corner);
        $this->assertLessThan(0.6 * count($subpaths[0]), $corner);
        // expect everything before to be a straight horizontal line
        for ($i = 1; $i <= $corner; ++$i) {
            [$xPrev, $yPrev] = $subpaths[0][$i - 1];
            [$x, $y] = $subpaths[0][$i];
            $this->assertGreaterThanOrEqual($xPrev, $x);
            $this->assertLessThanOrEqual(20, $x);
            $this->assertEqualsWithDelta($yPrev, $y, 10e-12);
        }
        // expect everything after to be a straight vertical line
        for ($i = $corner + 1; $i < count($subpaths[0]); ++$i) {
            [$xPrev, $yPrev] = $subpaths[0][$i - 1];
            [$x, $y] = $subpaths[0][$i];
            $this->assertEqualsWithDelta($xPrev, $x, 10e-12);
            $this->assertGreaterThanOrEqual($yPrev, $y);
            $this->assertLessThanOrEqual(30, $y);
        }

        // Ensure that with larger transform scale, the number of points increases (but not by too much)
        $transform = Transform::identity();
        $transform->scale(4, 4);
        $approx = new PathApproximator($transform);
        $approx->approximate([
            ['id' => 'M', 'args' => [10, 20]],
            ['id' => 'Q', 'args' => [15, 20, 20, 20]],
            ['id' => 'q', 'args' => [0, 5, 0, 10]],
        ]);
        $subpaths2 = $approx->getSubpaths();
        $this->assertGreaterThan(3.5 * count($subpaths[0]), count($subpaths2[0]));
        $this->assertLessThan(4.5 * count($subpaths[0]), count($subpaths2[0]));
    }

    public function testCurveToQuadraticSmooth(): void
    {
        // without preceding CurveToQuadratic
        $approx = new PathApproximator(Transform::identity());
        $approx->approximate([
            ['id' => 'M', 'args' => [10, 20]],
            ['id' => 'T', 'args' => [20, 20]],
        ]);
        $subpaths = $approx->getSubpaths();
        $this->assertCount(1, $subpaths);
        $this->assertGreaterThan(10, count($subpaths[0]));
        // check end points
        $this->assertEquals([10, 20], $subpaths[0][0]);
        $this->assertEquals([20, 20], $subpaths[0][count($subpaths[0]) - 1]);
        // check center (the curve should represent a flat horizontal line)
        [$x, $y] = $subpaths[0][ceil(count($subpaths[0]) / 2)];
        $this->assertEqualsWithDelta(15, $x, 1.0);
        $this->assertEqualsWithDelta(20, $y, 1.0);

        // with preceding CurveToQuadratic (test reflection of control point)
        // this also checks the relative variant
        $approx = new PathApproximator(Transform::identity());
        $approx->approximate([
            ['id' => 'M', 'args' => [10, 20]],
            ['id' => 'Q', 'args' => [15, 30, 20, 20]],
            ['id' => 't', 'args' => [10, 0]],
        ]);
        $subpaths = $approx->getSubpaths();
        $this->assertCount(1, $subpaths);
        $this->assertGreaterThan(10, count($subpaths[0]));
        // check end points
        $this->assertEquals([10, 20], $subpaths[0][0]);
        $this->assertEquals([30, 20], $subpaths[0][count($subpaths[0]) - 1]);
        // The curve should first bend down due to 'Q', then bend up due to the reflected 't'.
        // - check at 1/4 from beginning
        [$x, $y] = $subpaths[0][ceil(count($subpaths[0]) * 0.25)];
        $this->assertEqualsWithDelta(15, $x, 1.0);
        $this->assertEqualsWithDelta(25, $y, 1.0);
        // - check at 1/2 from beginning
        [$x, $y] = $subpaths[0][ceil(count($subpaths[0]) * 0.5)];
        $this->assertEqualsWithDelta(20, $x, 1.0);
        $this->assertEqualsWithDelta(20, $y, 1.0);
        // - check at 3/4 from beginning
        [$x, $y] = $subpaths[0][ceil(count($subpaths[0]) * 0.75)];
        $this->assertEqualsWithDelta(25, $x, 1.0);
        $this->assertEqualsWithDelta(15, $y, 1.0);
    }

    public function testArcTo(): void
    {
        // Run the following test once for the absolute command and once for the relative command.
        // The coordinates are chosen so that the output should be equal.
        $commands = [
            ['id' => 'A', 'args' => [5, 10, 0, 1, 0, 20, 20]],
            ['id' => 'a', 'args' => [5, 10, 0, 1, 0, 10, 0]],
        ];
        foreach ($commands as $command) {
            $approx = new PathApproximator(Transform::identity());
            $approx->approximate([
                ['id' => 'M', 'args' => [10, 20]],
                $command,
            ]);
            $subpaths = $approx->getSubpaths();
            $this->assertCount(1, $subpaths);
            $this->assertGreaterThan(10, count($subpaths[0]));
            // check end points
            $this->assertEquals([10, 20], $subpaths[0][0]);
            $this->assertEquals([20, 20], $subpaths[0][count($subpaths[0]) - 1]);
            // points in between should have x >= 10 && x <= 20 and y >= 20, y <= 30
            foreach ($subpaths[0] as $point) {
                [$x, $y] = $point;
                $this->assertGreaterThanOrEqual(10, $x);
                $this->assertLessThanOrEqual(20, $x);
                $this->assertGreaterThanOrEqual(20, $y);
                $this->assertLessThanOrEqual(30, $y);
            }
            // midpoint should roughly be at [15, 30]
            [$x, $y] = $subpaths[0][ceil(count($subpaths[0]) / 2)];
            $this->assertEqualsWithDelta(15, $x, 0.5);
            $this->assertEqualsWithDelta(30, $y, 0.5);
        }

        // Ensure that with larger transform scale, the number of points increases (but not by too much)
        $transform = Transform::identity();
        $transform->scale(4, 4);
        $approx = new PathApproximator($transform);
        $approx->approximate([
            ['id' => 'M', 'args' => [10, 20]],
            ['id' => 'A', 'args' => [5, 10, 0, 1, 0, 20, 20]],
        ]);
        $subpaths2 = $approx->getSubpaths();
        $this->assertGreaterThan(3.5 * count($subpaths[0]), count($subpaths2[0]));
        $this->assertLessThan(4.5 * count($subpaths[0]), count($subpaths2[0]));
    }

    public function testClosePath(): void
    {
        // try with both 'z' and 'Z', which should behave the same
        foreach (['z', 'Z'] as $closeCommand) {
            $approx = new PathApproximator(Transform::identity());
            $approx->approximate([
                ['id' => 'M', 'args' => [10, 20]],
                ['id' => 'L', 'args' => [50, 70]],
                ['id' => $closeCommand, 'args' => []],
                ['id' => 'l', 'args' => [13, 17]],
            ]);
            $this->assertSame([
                [
                    [10.0, 20.0],
                    [50.0, 70.0],
                    [10.0, 20.0],
                ],
                [
                    [10.0, 20.0],
                    [23.0, 37.0],
                ],
            ], $approx->getSubpaths());

            // https://www.w3.org/TR/SVG/paths.html#PathDataClosePathCommand
            // "This path segment may be of zero length."
            $approx = new PathApproximator(Transform::identity());
            $approx->approximate([
                ['id' => 'M', 'args' => [10, 20]],
                ['id' => $closeCommand, 'args' => []],
                ['id' => 'M', 'args' => [30, 50]],
                ['id' => 'L', 'args' => [60, 100]],
                ['id' => 'L', 'args' => [30, 50]],
                ['id' => $closeCommand, 'args' => []],
                ['id' => $closeCommand, 'args' => []],
            ]);
            $this->assertSame([
                [
                    [10.0, 20.0],
                    [10.0, 20.0],
                ],
                [
                    [30.0, 50.0],
                    [60.0, 100.0],
                    [30.0, 50.0],
                    [30.0, 50.0],
                ],
                [
                    [30.0, 50.0],
                    [30.0, 50.0],
                ],
            ], $approx->getSubpaths());
        }
    }
}
