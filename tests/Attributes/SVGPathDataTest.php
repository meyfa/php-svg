<?php

namespace SVG\Attributes;

use SVG\Attributes\PathData\ArcCurve;
use SVG\Attributes\PathData\BezierCurve;
use SVG\Attributes\PathData\ClosePath;
use SVG\Attributes\PathData\HorizontalLine;
use SVG\Attributes\PathData\Line;
use SVG\Attributes\PathData\Move;
use SVG\Attributes\PathData\PathDataCommandInterface;
use SVG\Attributes\PathData\QuadraticCurve;
use SVG\Attributes\PathData\RelativeArcCurve;
use SVG\Attributes\PathData\RelativeBezierCurve;
use SVG\Attributes\PathData\RelativeHorizontalLine;
use SVG\Attributes\PathData\RelativeLine;
use SVG\Attributes\PathData\RelativeMove;
use SVG\Attributes\PathData\RelativeQuadraticCurve;
use SVG\Attributes\PathData\RelativeVerticalLine;
use SVG\Attributes\PathData\VerticalLine;
use SVG\Attributes\SVGPathData;

class SVGPathDataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider parseDataProvider
     *
     * @param class-string<PathDataInstructionInterface> $expectedClasses
     */
    public function testFromString(string $pathData, array $expectedClasses): void
    {
        $pathData = SVGPathData::fromString($pathData);
        $instructions = array_values(array_reverse(iterator_to_array($pathData)));

        $this->assertCount(count($expectedClasses), $pathData);

        foreach ($instructions as $key => $instruction) {
            $this->assertInstanceOf($expectedClasses[$key], $instruction);
        }
    }

    public function parseDataProvider(): array
    {
        return [
            ['M 10 10', [Move::class]],
            ['M 0 0 m 10 10', [Move::class, RelativeMove::class]],
            ['M 10 10 m 20 20 z', [Move::class, RelativeMove::class, ClosePath::class]],
            ['M 0 0 C 10 10 20 10 30 0', [Move::class, BezierCurve::class]],
            ['M 0 0 c 10 10 10 0 10 0', [Move::class, RelativeBezierCurve::class]],
            ['M 0 0 s 10 10 10 0', [Move::class, RelativeBezierCurve::class]],
            ['M 0 0 L 10 10 l 10 0', [Move::class, Line::class, RelativeLine::class]],
            ['M 0 0 H 10 h 10', [Move::class, HorizontalLine::class, RelativeHorizontalLine::class]],
            ['M 0 0 V 10 v 10', [Move::class, VerticalLine::class, RelativeVerticalLine::class]],
            ['M 10 315 L 110 215 A 36 60 0 0 1 150.71 170.29', [Move::class, Line::class, ArcCurve::class]],
            ['M 0 0 a 20 20 0 0 1 20 20', [Move::class, RelativeArcCurve::class]],
            ['M 10 80 Q 95 10 180 80', [Move::class, QuadraticCurve::class]],
            ['M 10 80 q 95 10 170 0', [Move::class, RelativeQuadraticCurve::class]]
        ];
    }

    /**
     * @dataProvider toStringProvider
     *
     * @param PathDataInstructionInterface[] $instructions
     */
    public function testToString(array $instructions, string $expectedPath): void
    {
        $pathData = new SVGPathData();

        foreach ($instructions as $instruction) {
            $pathData->addCommand($instruction);
        }

        $this->assertSame($expectedPath, $pathData->__toString());
    }

    public function toStringProvider(): array
    {
        return [
            [[new Move(10, 10)], 'M 10 10'],
            [[new Move(0, 0), new RelativeMove(10, 10)], 'M 0 0 m 10 10'],
            [[new Move(10, 10), new RelativeMove(20, 20), new ClosePath()], 'M 10 10 m 20 20 z'],
            [[new Move(10, 0), new RelativeLine(20, 20)], "M 10 0 l 20 20"],
            [[new Move(10, 0), new HorizontalLine(20)], "M 10 0 H 20"],
            [[new Move(10, 0), new RelativeHorizontalLine(20)], "M 10 0 h 20"],
            [[new Move(10, 0), new VerticalLine(20)], "M 10 0 V 20"],
            [[new Move(10, 0), new RelativeVerticalLine(20)], "M 10 0 v 20"],
            [[new Move(0, 0), new QuadraticCurve(0, 20, 20, 20)], "M 0 0 Q 0 20 20 20"],
            [[new Move(0, 0), new RelativeQuadraticCurve(0, 20, 20, 20)], "M 0 0 q 0 20 20 20"],
            [[new Move(10, 315), new Line(110, 215), new ArcCurve(36, 60, 0, false, true, 150.71, 170.29)], 'M 10 315 L 110 215 A 36 60 0 0 1 150.71 170.29'],
            [[new Move(10, 10), new RelativeArcCurve(30, 30, 0, false, true, 100, 100)], 'M 10 10 a 30 30 0 0 1 100 100'],
            [[new Move(10, 10), new BezierCurve(10, 10, 30, 10, 10, 10)], 'M 10 10 C 10 10 30 10 10 10'],
            [[new Move(10, 10), new RelativeBezierCurve(10, 10, 30, 10, 10, 10)], 'M 10 10 c 10 10 30 10 10 10'],
            [[new Move(0, 0), new QuadraticCurve(20, 20)], "M 0 0 T 20 20"],
            [[new Move(0, 0), new RelativeQuadraticCurve(20, 20)], "M 0 0 t 20 20"],
            [[new Move(10, 10), new BezierCurve(30, 10, 10, 10)], 'M 10 10 S 30 10 10 10'],
            [[new Move(10, 10), new RelativeBezierCurve(30, 10, 10, 10)], 'M 10 10 s 30 10 10 10'],
        ];
    }

    /**
     * @dataProvider pointsProvider
     *
     * @param PathDataInstructionInterface[] $instructions
     * @param {1: float, 2: float}[] $expectedPoints
     */
    public function testPoints(array $instructions, array $expectedPoints): void
    {
        $pathData = new SVGPathData();

        foreach ($instructions as $instruction) {
            $pathData->addCommand($instruction);
        }

        foreach ($pathData as $command) {
            // commands are coming in reverse order, but their points are coming normally
            // therefore we need to reverse them to mach order of expected points
            $points = array_reverse($command->getPoints());

            $lastPoint = $command->getLastPoint();
            $expectedLastpoint = end($expectedPoints);

            $this->assertEquals(
                $expectedLastpoint[0],
                $lastPoint[0],
            );

            $this->assertEquals(
                $expectedLastpoint[1],
                $lastPoint[1],
            );

            foreach ($points as $point) {
                $expectedPoint = array_pop($expectedPoints);

                $this->assertEquals(
                    $expectedPoint[0],
                    $point[0],
                );

                $this->assertEquals(
                    $expectedPoint[1],
                    $point[1],
                );
            }
        }

        $this->assertEmpty($expectedPoints);
    }

    public function pointsProvider(): array
    {
        return [
            [[new Move(0, 0), new ArcCurve(10, 10, 0, 0, 1, 10, 10)], [[0, 0], [10, 10]]],
            [[new Move(0, 0), new BezierCurve(10, 10, 20, 10, 30, 0)], [[0, 0], [10, 10], [20, 10], [30, 0]]],
            [[new Move(0, 0), new BezierCurve(10, 10, 30, 0)], [[0, 0], [10, 10], [30, 0]]],
            [[new Move(0, 0), new ClosePath()], [[0, 0]]],
            [[new Move(0, 0), new HorizontalLine(10)], [[0, 0], [10, 0]]],
            [[new Move(0, 0), new VerticalLine(10)], [[0, 0], [0, 10]]],
            [[new Move(0, 0), new Line(10, 10)], [[0, 0], [10, 10]]],
            [[new Move(0, 0), new QuadraticCurve(10, 10, 20, 0)], [[0, 0], [10, 10], [20, 0]]],
            [[new Move(0, 0), new QuadraticCurve(20, 0)], [[0, 0], [20, 0]]],

            [[new Move(10, 10), new RelativeArcCurve(10, 10, 0, 0, 1, 10, 10)], [[10, 10], [20, 20]]],
            [[new Move(10, 10), new RelativeBezierCurve(10, 10, 20, 10, 30, 0)], [[10, 10], [20, 20], [30, 20], [40, 10]]],
            [[new Move(10, 10), new RelativeBezierCurve(10, 10, 30, 0)], [[10, 10], [20, 20], [40, 10]]],
            [[new Move(10, 10), new RelativeHorizontalLine(10)], [[10, 10], [20, 10]]],
            [[new Move(10, 10), new RelativeVerticalLine(10)], [[10, 10], [10, 20]]],
            [[new Move(10, 10), new RelativeLine(10, 10)], [[10, 10], [20, 20]]],
            [[new Move(10, 10), new RelativeQuadraticCurve(10, 10, 20, 0)], [[10, 10], [20, 20], [30, 10]]],
            [[new Move(10, 10), new RelativeQuadraticCurve(20, 0)], [[10, 10], [30, 10]]],
        ];
    }
}
