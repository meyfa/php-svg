<?php

namespace SVG;

use SVG\Rasterization\Transform\Transform;
use SVG\Rasterization\Transform\TransformParser;

/**
 * @coversDefaultClass \SVG\Rasterization\Transform\TransformParser
 *
 * @SuppressWarnings(PHPMD)
 */
class TransformParserTest extends \PHPUnit\Framework\TestCase
{
    private function assertMap(Transform $t, array $expected, array $source)
    {
        $t->map($source[0], $source[1]);
        $this->assertEquals($expected, $source);
    }

    public function testParseTransformString()
    {
        $transform = TransformParser::parseTransformString('translate(10,20) scale(3,7) rotate(90)');
        $this->assertMap($transform, [-290, 720], [100, 100]);

        // should not care about whitespace
        $transform = TransformParser::parseTransformString("  translate  (  10  ,  20  ) \nscale(3, 7) rotate(90)  ");
        $this->assertMap($transform, [-290, 720], [100, 100]);

        // should not fail for missing arguments
        $this->assertNotNull(TransformParser::parseTransformString('translate(10)'));
    }
}
