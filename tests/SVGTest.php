<?php

use SVG\SVG;

/**
 * @SuppressWarnings(PHPMD)
 */
class SVGTest extends PHPUnit_Framework_TestCase
{
    public function testConvertUnit()
    {
        // units
        $this->assertEquals(16, SVG::convertUnit('12pt', 100));
        $this->assertEquals(16, SVG::convertUnit('1pc', 100));
        $this->assertEquals(37.8, SVG::convertUnit('1cm', 100), '', 0.01);
        $this->assertEquals(37.8, SVG::convertUnit('10mm', 100), '', 0.01);
        $this->assertEquals(96, SVG::convertUnit('1in', 100));
        $this->assertEquals(50, SVG::convertUnit('50%', 100));
        $this->assertEquals(16, SVG::convertUnit('16px', 100));

        // no unit
        $this->assertEquals(16, SVG::convertUnit('16', 100));

        // number
        $this->assertEquals(16, SVG::convertUnit(16, 100));

        // illegal: missing number
        $this->assertNull(SVG::convertUnit('px', 100));
        $this->assertNull(SVG::convertUnit('', 100));
    }
}
