<?php

namespace SVG;

use SVG\Rasterization\Path\PathParser;

/**
 * @covers \SVG\Rasterization\Path\PathParser
 *
 * @SuppressWarnings(PHPMD)
 */
class PathParserTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldSplitCorrectly()
    {
        $obj = new PathParser();

        // should split commands and arguments correctly
        $this->assertEquals([
            ['id' => 'M', 'args' => [10, 10]],
            ['id' => 'l', 'args' => [10, -10]],
            ['id' => 'h', 'args' => [50]],
            ['id' => 'v', 'args' => [10]],
            ['id' => 'l', 'args' => [7, -7]],
            ['id' => 'h', 'args' => [0.5]],
            ['id' => 'z', 'args' => []],
            ['id' => 'H', 'args' => [0]],
            ['id' => 'z', 'args' => []],
        ], $obj->parse(' M10,10 l +10 -10 h .5e2 v 100e-1 l7-7 h.5 z H0z'));
    }

    public function testShouldSupportRepeatedCommands()
    {
        $obj = new PathParser();

        // should support commands repeated implicitly (e.g. 'L 10,10 20,20')
        $this->assertEquals([
            ['id' => 'L', 'args' => [10, 10]],
            ['id' => 'L', 'args' => [20, 20]],
            ['id' => 'h', 'args' => [5]],
            ['id' => 'h', 'args' => [5]],
            ['id' => 'h', 'args' => [5]],
            ['id' => 'q', 'args' => [10, 10, 20, 20]],
            ['id' => 'q', 'args' => [50, 50, 60, 60]],
        ], $obj->parse('L10,10 20,20 h 5 5 5 q 10 10 20 20 50 50 60 60'));
    }

    public function testShouldTreatImplicitMoveToLikeLineTo()
    {
        $obj = new PathParser();

        // should treat repeated MoveTo commands like implicit LineTo commands
        $this->assertEquals([
            ['id' => 'M', 'args' => [10, 10]],
            ['id' => 'L', 'args' => [20, 20]],
            ['id' => 'L', 'args' => [20, 10]],
            ['id' => 'm', 'args' => [-10, 0]],
            ['id' => 'l', 'args' => [-10, -5]],
        ], $obj->parse('M10,10 20,20, 20,10 m-10,0 -10,-5'));
    }

    public function testShouldAbortOnError()
    {
        $obj = new PathParser();

        // should return path up until erronous sequence
        $this->assertEquals([
            ['id' => 'M', 'args' => [10, 10]],
            ['id' => 'L', 'args' => [30, 30]],
        ], $obj->parse('M10,10 L30,30 C 5 z'));
    }
}
