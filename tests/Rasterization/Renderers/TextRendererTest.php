<?php

namespace SVG\Rasterization\Renderers;

use AssertGD\GDSimilarityConstraint;
use SVG\Rasterization\SVGRasterizer;

/**
 * @requires extension gd
 * @covers \SVG\Rasterization\Renderers\TextRenderer
 *
 * @SuppressWarnings(PHPMD)
 */
class TextRendererTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldNotFailWithoutRegisteredFont()
    {
        $obj = new TextRenderer();

        $context = $this->getMockForAbstractClass('\SVG\Nodes\SVGNode');

        $rasterizer = new SVGRasterizer('40px', '80px', null, 4, 8);
        $obj->render($rasterizer, [
            'x'          => 10,
            'y'          => 10,
            'fontFamily' => 'Roboto',
            'fontWeight' => 'normal',
            'fontStyle'  => 'normal',
            'fontSize'   => 16,
            'anchor'     => 'middle',
            'text'       => 'foo',
        ], $context);
        $img = $rasterizer->finish();

        $this->assertThat($img, new GDSimilarityConstraint('./tests/images/empty-4x8.png'));
    }
}
