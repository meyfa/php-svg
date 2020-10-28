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
        $this->assertEquals(array(
            array('id' => 'M', 'args' => array(10, 10)),
            array('id' => 'l', 'args' => array(10, -10)),
            array('id' => 'h', 'args' => array(50)),
            array('id' => 'v', 'args' => array(10)),
            array('id' => 'l', 'args' => array(7, -7)),
            array('id' => 'h', 'args' => array(0.5)),
            array('id' => 'z', 'args' => array()),
            array('id' => 'H', 'args' => array(0)),
            array('id' => 'z', 'args' => array()),
        ), $obj->parse(' M10,10 l +10 -10 h .5e2 v 100e-1 l7-7 h.5 z H0z'));
    }

    public function testShouldSupportRepeatedCommands()
    {
        $obj = new PathParser();

        // should support commands repeated implicitly (e.g. 'L 10,10 20,20')
        $this->assertEquals(array(
            array('id' => 'L', 'args' => array(10, 10)),
            array('id' => 'L', 'args' => array(20, 20)),
            array('id' => 'h', 'args' => array(5)),
            array('id' => 'h', 'args' => array(5)),
            array('id' => 'h', 'args' => array(5)),
            array('id' => 'q', 'args' => array(10, 10, 20, 20)),
            array('id' => 'q', 'args' => array(50, 50, 60, 60)),
        ), $obj->parse('L10,10 20,20 h 5 5 5 q 10 10 20 20 50 50 60 60'));
    }

    public function testShouldTreatImplicitMoveToLikeLineTo()
    {
        $obj = new PathParser();

        // should treat repeated MoveTo commands like implicit LineTo commands
        $this->assertEquals(array(
            array('id' => 'M', 'args' => array(10, 10)),
            array('id' => 'L', 'args' => array(20, 20)),
            array('id' => 'L', 'args' => array(20, 10)),
            array('id' => 'm', 'args' => array(-10, 0)),
            array('id' => 'l', 'args' => array(-10, -5)),
        ), $obj->parse('M10,10 20,20, 20,10 m-10,0 -10,-5'));
    }

    public function testShouldAbortOnError()
    {
        $obj = new PathParser();

        // should return path up until erronous sequence
        $this->assertEquals(array(
            array('id' => 'M', 'args' => array(10, 10)),
            array('id' => 'L', 'args' => array(30, 30)),
        ), $obj->parse('M10,10 L30,30 C 5 z'));
    }
}
