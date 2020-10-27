<?php

namespace SVG;

use SVG\Utilities\SVGStyleParser;

/**
 * @SuppressWarnings(PHPMD)
 */
class SVGStyleParserTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers SVG\Utilities\SVGStyleParser
     */
    public function testParseStylesWithEmptyString()
    {
        $this->assertCount(0, SVGStyleParser::parseStyles(''));
    }

    /**
     * @covers SVG\Utilities\SVGStyleParser
     */
    public function testParseCssWithMatchedElement()
    {
        $result = SVGStyleParser::parseCss('svg {background-color: beige;}');

        $this->assertSame('beige', $result['svg']['background-color']);
    }

    /**
     * @covers SVG\Utilities\SVGStyleParser
     */
    public function testParseCssWithSkippedElement()
    {
        $result = SVGStyleParser::parseCss('@font-face {font-family: "Bitstream Vera Serif Bold";}');

        $this->assertCount(0, $result);
    }
}
