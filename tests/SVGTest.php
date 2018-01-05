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

    public function testConvertAngleUnit()
    {
        // degrees
        $this->assertEquals(15.5, SVG::convertAngleUnit('15.5deg'));
        $this->assertEquals(-3600, SVG::convertAngleUnit('-3600deg'));
        $this->assertEquals(400, SVG::convertAngleUnit('+400deg'));

        // radians
        $this->assertEquals(0, SVG::convertAngleUnit('0rad'));
        $this->assertEquals(57.295779513, SVG::convertAngleUnit('1rad'), '', 0.00000001);
        $this->assertEquals(180, SVG::convertAngleUnit('3.14159265359rad'), 0.00000001);

        // gradians
        $this->assertEquals(0, SVG::convertAngleUnit('0grad'));
        $this->assertEquals(360, SVG::convertAngleUnit('400grad'));
        $this->assertEquals(-720, SVG::convertAngleUnit('-800grad'));

        // turns
        $this->assertEquals(0, SVG::convertAngleUnit('0turn'));
        $this->assertEquals(-360, SVG::convertAngleUnit('-1turn'));
        $this->assertEquals(540, SVG::convertAngleUnit('1.5turn'));

        // no unit
        $this->assertEquals(15.5, SVG::convertAngleUnit('15.5'));

        // number
        $this->assertEquals(15.5, SVG::convertAngleUnit(15.5));

        // illegal: missing number
        $this->assertNull(SVG::convertAngleUnit('deg'));
        $this->assertNull(SVG::convertAngleUnit(''));
    }

    public function testParseColor()
    {
        // named colors
        $this->assertEquals(array(0, 0, 0, 255), SVG::parseColor('black'));
        $this->assertEquals(array(255, 255, 255, 255), SVG::parseColor('white'));
        $this->assertEquals(array(250, 128, 114, 255), SVG::parseColor('salmon'));

        // transparency
        $this->assertEquals(array(0, 0, 0, 0), SVG::parseColor('transparent'));

        // invalid color name
        $this->assertEquals(array(0, 0, 0, 0), SVG::parseColor('doesnotexist'));

        // hex 3 (#RGB)
        $this->assertEquals(array(255, 255, 255, 255), SVG::parseColor('#FFF'));
        $this->assertEquals(array(255, 255, 255, 255), SVG::parseColor('#fff'));
        $this->assertEquals(array(0, 0, 0, 255), SVG::parseColor('#000'));
        $this->assertEquals(array(136, 170, 204, 255), SVG::parseColor('#8AC'));
        // hex 4 (#RGBA)
        $this->assertEquals(array(255, 255, 255, 153), SVG::parseColor('#FFF9'));
        $this->assertEquals(array(255, 255, 255, 153), SVG::parseColor('#fff9'));
        $this->assertEquals(array(0, 0, 0, 170), SVG::parseColor('#000A'));
        $this->assertEquals(array(136, 170, 204, 187), SVG::parseColor('#8ACB'));
        // hex 6 (#RRGGBB)
        $this->assertEquals(array(255, 255, 255, 255), SVG::parseColor('#FFFFFF'));
        $this->assertEquals(array(255, 255, 255, 255), SVG::parseColor('#ffffff'));
        $this->assertEquals(array(0, 0, 0, 255), SVG::parseColor('#000000'));
        $this->assertEquals(array(137, 171, 205, 255), SVG::parseColor('#89ABCD'));
        // hex 8 (#RRGGBBAA)
        $this->assertEquals(array(255, 255, 255, 153), SVG::parseColor('#FFFFFF99'));
        $this->assertEquals(array(255, 255, 255, 153), SVG::parseColor('#ffffff99'));
        $this->assertEquals(array(0, 0, 0, 170), SVG::parseColor('#000000AA'));
        $this->assertEquals(array(137, 171, 205, 187), SVG::parseColor('#89ABCDBB'));

        // invalid hex
        $this->assertEquals(array(0, 0, 0, 0), SVG::parseColor('#FF'));
        $this->assertEquals(array(0, 0, 0, 0), SVG::parseColor('#FFFFFFF'));
        $this->assertEquals(array(0, 0, 0, 0), SVG::parseColor('#GGG'));
        $this->assertEquals(array(0, 0, 0, 0), SVG::parseColor('#GGGGGG'));
        $this->assertEquals(array(0, 0, 0, 0), SVG::parseColor('##FFFFFF'));

        // rgb(a) - without alpha component
        // - standard
        $this->assertEquals(array(255, 255, 255, 255), SVG::parseColor('rgb(255, 255, +255)'));
        $this->assertEquals(array(0, 0, 0, 255), SVG::parseColor('rgb(0, 0, 0)'));
        // - percentages
        $this->assertEquals(array(255, 127, 0, 255), SVG::parseColor('rgb(100%, +50%, 0%)'));
        // - delimiters
        $this->assertEquals(array(136, 170, 204, 255), SVG::parseColor('rgba(136,170,204)'));
        $this->assertEquals(array(136, 170, 204, 255), SVG::parseColor('rgb(  136  ,  170  ,  204  )'));
        $this->assertEquals(array(136, 170, 204, 255), SVG::parseColor('rgb(136 170.3 204.9)'));
        // - out of range
        $this->assertEquals(array(255, 0, 255, 255), SVG::parseColor('rgb(255, -10, 1000)'));
        // - floating point
        $this->assertEquals(array(136, 170, 204, 255), SVG::parseColor('rgb(136, +170.3, 204.9)'));
        // - rgba keyword (alias)
        $this->assertEquals(array(136, 170, 204, 255), SVG::parseColor('rgba(136, 170.3, 204.9)'));
        $this->assertEquals(array(136, 170, 204, 255), SVG::parseColor('rgba(136 170.3, 204.9)'));
        // rgb(a) - with alpha component
        // - standard
        $this->assertEquals(array(255, 255, 255, 255), SVG::parseColor('rgb(255, 255, 255, 1)'));
        $this->assertEquals(array(255, 255, 255, 127), SVG::parseColor('rgb(255, 255, 255, 0.5)'));
        $this->assertEquals(array(255, 255, 255, 127), SVG::parseColor('rgb(255, 255, 255, +.5)'));
        // - percentages
        $this->assertEquals(array(255, 127, 0, 127), SVG::parseColor('rgb(100%, 50%, 0%, 50%)'));
        // - delimiters
        $this->assertEquals(array(136, 170, 204, 127), SVG::parseColor('rgb(136,170,204,.5)'));
        $this->assertEquals(array(136, 170, 204, 255), SVG::parseColor('rgb(  136  ,  170  ,  204  ,  1  )'));
        $this->assertEquals(array(136, 170, 204, 127), SVG::parseColor('rgb(136 170 204 .5)'));
        $this->assertEquals(array(136, 170, 204, 127), SVG::parseColor('rgb(136, 170 204 / .5)'));
        $this->assertEquals(array(136, 170, 204, 127), SVG::parseColor('rgb(136, 170 204 / 50%)'));
        // - out of range
        $this->assertEquals(array(255, 0, 255, 255), SVG::parseColor('rgb(255, -10, 1000, 1.9)'));
        $this->assertEquals(array(255, 0, 0, 0), SVG::parseColor('rgb(255, -10, -10, -.1)'));
        // rgba keyword (alias)
        $this->assertEquals(array(255, 255, 255, 127), SVG::parseColor('rgba(255, 255, 255, .5)'));
        $this->assertEquals(array(0, 0, 0, 127), SVG::parseColor('rgba(0, 0, 0, 50%)'));
        $this->assertEquals(array(255, 255, 255, 127), SVG::parseColor('rgba(100%, 100%, 100% / 50%)'));

        // invalid rgb(a)
        $this->assertEquals(array(0, 0, 0, 0), SVG::parseColor('rgb(136, 170)'));
        $this->assertEquals(array(0, 0, 0, 0), SVG::parseColor('rgb(136, , 204)'));
        $this->assertEquals(array(0, 0, 0, 0), SVG::parseColor('rgb (136, 170, 204)'));
        $this->assertEquals(array(0, 0, 0, 0), SVG::parseColor('rgb(136, 170, 204'));
        $this->assertEquals(array(0, 0, 0, 0), SVG::parseColor('rgba(136, 170, 204, )'));
        $this->assertEquals(array(0, 0, 0, 0), SVG::parseColor('rgb (136, 170, 204, 0.5)'));
        $this->assertEquals(array(0, 0, 0, 0), SVG::parseColor('rgb(136, 170, 204, 0.5'));
        $this->assertEquals(array(0, 0, 0, 0), SVG::parseColor('rgba(136, 170, 204, 0.5, )'));

        // hsl(a) - without alpha component
        // - standard
        $this->assertEquals(array(0, 0, 0, 255), SVG::parseColor('hsl(0, 0%, 0%)'));
        $this->assertEquals(array(255, 255, 255, 255), SVG::parseColor('hsl(0, 0%, 100%)'));
        $this->assertEquals(array(255, 0, 0, 255), SVG::parseColor('hsl(0, 100%, 50%)'));
        $this->assertEquals(array(0, 255, 0, 255), SVG::parseColor('hsl(120, 100%, 50%)'));
        $this->assertEquals(array(0, 0, 255, 255), SVG::parseColor('hsl(240, 100%, 50%)'));
        $this->assertEquals(array(89, 98, 80, 255), SVG::parseColor('hsl(90, 10%, 35%)'));
        // - units
        $this->assertEquals(array(89, 98, 80, 255), SVG::parseColor('hsl(90deg, 10%, 35%)'));
        $this->assertEquals(array(89, 98, 80, 255), SVG::parseColor('hsl(1.570796327rad, 10%, 35%)'));
        $this->assertEquals(array(89, 98, 80, 255), SVG::parseColor('hsl(100grad, 10%, 35%)'));
        $this->assertEquals(array(89, 98, 80, 255), SVG::parseColor('hsl(.25turn, 10%, 35%)'));
        // - delimiters
        $this->assertEquals(array(89, 98, 80, 255), SVG::parseColor('hsl(90deg,10%,35%)'));
        $this->assertEquals(array(89, 98, 80, 255), SVG::parseColor('hsl(  90deg , 10% , 35%  )'));
        $this->assertEquals(array(89, 98, 80, 255), SVG::parseColor('hsl(90deg 10% 35%)'));
        // - out of range
        $this->assertEquals(array(89, 98, 80, 255), SVG::parseColor('hsl(3690deg,10%,35%)'));
        $this->assertEquals(array(89, 98, 80, 255), SVG::parseColor('hsl(-3510deg,10%,35%)'));
        $this->assertEquals(array(255, 255, 255, 255), SVG::parseColor('hsl(0, -50%, +200%)'));
        // - hsla keyword (alias)
        $this->assertEquals(array(127, 127, 127, 255), SVG::parseColor('hsla(0, 0%, 50%)'));
        $this->assertEquals(array(255, 255, 255, 255), SVG::parseColor('hsla(0, 0%, 100%)'));
        $this->assertEquals(array(89, 98, 80, 255), SVG::parseColor('hsla(90, 10%, 35%)'));
        // hsl(a) - with alpha component
        // - standard
        $this->assertEquals(array(0, 0, 0, 255), SVG::parseColor('hsl(0, 0%, 0%, 1)'));
        $this->assertEquals(array(255, 255, 255, 127), SVG::parseColor('hsl(0, 0%, 100%, +.5)'));
        // - percentages
        $this->assertEquals(array(255, 255, 255, 127), SVG::parseColor('hsl(0, 0%, 100%, 50%)'));
        // - delimiters
        $this->assertEquals(array(255, 0, 0, 127), SVG::parseColor('hsl(  0 100% 50% .5  )'));
        $this->assertEquals(array(255, 0, 0, 127), SVG::parseColor('hsl(0 100%, 50% / .5)'));
        $this->assertEquals(array(255, 0, 0, 127), SVG::parseColor('hsl(0 100%, 50% / 50%)'));
        // - out of range
        $this->assertEquals(array(0, 255, 0, 0), SVG::parseColor('hsl(120, 100%, 50%, -.1)'));
        $this->assertEquals(array(0, 255, 0, 255), SVG::parseColor('hsl(120, 100%, 50%, 1.9)'));
        // - hsla keyword (alias)
        $this->assertEquals(array(255, 0, 0, 127), SVG::parseColor('hsla(  0 100% 50% .5  )'));
        $this->assertEquals(array(255, 0, 0, 127), SVG::parseColor('hsla(0 100%, 50% / .5)'));
        $this->assertEquals(array(255, 0, 0, 127), SVG::parseColor('hsla(0 100%, 50% / 50%)'));

        // invalid hsl(a)
        $this->assertEquals(array(0, 0, 0, 0), SVG::parseColor('hsl(deg, 100%, 0%)'));
        $this->assertEquals(array(0, 0, 0, 0), SVG::parseColor('hsl(90, 100%)'));
        $this->assertEquals(array(0, 0, 0, 0), SVG::parseColor('hsl (90, 10%, 35%)'));
        $this->assertEquals(array(0, 0, 0, 0), SVG::parseColor('hsl(90, 10%, 35%'));
        $this->assertEquals(array(0, 0, 0, 0), SVG::parseColor('hsla(90, 10%, 35%, )'));
        $this->assertEquals(array(0, 0, 0, 0), SVG::parseColor('hsl (90, 10%, 35%, 0.5)'));
        $this->assertEquals(array(0, 0, 0, 0), SVG::parseColor('hsl(136, 170, 204, 0.5'));
        $this->assertEquals(array(0, 0, 0, 0), SVG::parseColor('hsla(136, 170, 204, 0.5, )'));
    }
}
