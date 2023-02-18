<?php

namespace SVG\Attributes;

use SVG\Attributes\PathData\ClosePath;
use SVG\Attributes\PathData\Move;
use SVG\Attributes\PathData\PathDataCommandInterface;
use SVG\Attributes\PathData\RelativeMove;
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
        ];
    }
}
