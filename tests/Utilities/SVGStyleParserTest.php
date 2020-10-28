<?php

namespace SVG;

use SVG\Utilities\SVGStyleParser;

/**
 * @covers SVG\Utilities\SVGStyleParser
 *
 * @SuppressWarnings(PHPMD)
 */
class SVGStyleParserTest extends \PHPUnit\Framework\TestCase
{
    public function testParseStylesWithEmptyString()
    {
        $this->assertCount(0, SVGStyleParser::parseStyles(''));
    }

    public function testParseCssWithMatchedElement()
    {
        $result = SVGStyleParser::parseCss('svg {background-color: beige;}');

        $this->assertSame('beige', $result['svg']['background-color']);
    }

    public function testParseCssWithSkippedElement()
    {
        $result = SVGStyleParser::parseCss('@font-face {font-family: "Bitstream Vera Serif Bold";}');

        $this->assertCount(0, $result);
    }
}
