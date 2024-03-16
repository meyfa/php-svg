<?php

namespace SVG\Tests\Utilities;

use PHPUnit\Framework\TestCase;
use SVG\Utilities\SVGStyleParser;

/**
 * @covers \SVG\Utilities\SVGStyleParser
 *
 * @SuppressWarnings(PHPMD)
 */
class SVGStyleParserTest extends TestCase
{
    public function testParseStylesWithEmptyString(): void
    {
        $this->assertCount(0, SVGStyleParser::parseStyles(''));
    }

    public function testParseCssWithMatchedElement(): void
    {
        $result = SVGStyleParser::parseCss('svg {background-color: beige;}');

        $this->assertSame('beige', $result['svg']['background-color']);
    }

    public function testParseCssWithSkippedElement(): void
    {
        $result = SVGStyleParser::parseCss('@font-face {font-family: "Bitstream Vera Serif Bold";}');

        $this->assertCount(0, $result);
    }

    public function testParseDuplicateSelectors(): void
    {
        $result = SVGStyleParser::parseCss('svg {background-color: beige;}; svg {stroke: none;} svg { fill: blue }');

        $this->assertSame(['svg' => ['background-color' => 'beige', 'stroke' => 'none', 'fill' => 'blue']], $result);
    }
}
