<?php

namespace SVG;

use AssertGD\GDSimilarityConstraint;
use SVG\Rasterization\Renderers\PolygonRenderer;
use SVG\Rasterization\SVGRasterizer;

/**
 * @requires extension gd
 * @covers \SVG\Rasterization\Renderers\PolygonRenderer
 *
 * @SuppressWarnings(PHPMD)
 */
class PolygonRendererTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldNotFailForTooFewPoints()
    {
        // ensures that there is no crash in case fewer than 3 points are provided,
        // which might trip up the fill or stroke algorithms if they don't check for it
        $this->expectNotToPerformAssertions();

        $obj = new PolygonRenderer();

        $context = $this->getMockForAbstractClass('\SVG\Nodes\SVGNode');
        $context->setStyle('fill', '#FF0000');
        $context->setStyle('stroke', '#0000FF');
        $context->setStyle('stroke-width', '1px');

        $rasterizer = new SVGRasterizer('50px', '50px', null, 50, 50);

        // try with 2 points
        $obj->render($rasterizer, [
            'points' => [[0, 0], [10, 10]],
            'open' => false,
            'fill-rule' => 'nonzero',
        ], $context);

        // then with 1
        $obj->render($rasterizer, [
            'points' => [[0, 0]],
            'open' => false,
            'fill-rule' => 'nonzero',
        ], $context);

        // then with 0
        $obj->render($rasterizer, [
            'points' => [],
            'open' => false,
            'fill-rule' => 'nonzero',
        ], $context);
    }

    public function testShouldRespectFillRule()
    {
        $obj = new PolygonRenderer();

        $context = $this->getMockForAbstractClass('\SVG\Nodes\SVGNode');
        $context->setStyle('fill', '#FF0000');
        $context->setStyle('stroke', 'none');

        $points = [
            [5, 5],
            [45, 5],
            [40, 40],
            [10, 10],
            [10, 40],
            [40, 10],
            [45, 45],
            [5, 45],
        ];

        foreach (['nonzero', 'evenodd'] as $fillRule) {
            $rasterizer = new SVGRasterizer('50px', '50px', null, 50, 50);
            $obj->render($rasterizer, [
                'points' => $points,
                'open' => false,
                'fill-rule' => $fillRule,
            ], $context);
            $img = $rasterizer->finish();

            $file = './tests/images/renderer-polygon-' . $fillRule . '.png';
            $this->assertThat($img, new GDSimilarityConstraint($file));
        }
    }
}
