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
    public function testShouldRespectFillRule()
    {
        $obj = new PolygonRenderer();

        $context = $this->getMockForAbstractClass('\SVG\Nodes\SVGNode');
        $context->setStyle('fill', '#FF0000');
        $context->setStyle('stroke', 'none');

        $points = array(
            array(5, 5),
            array(45, 5),
            array(40, 40),
            array(10, 10),
            array(10, 40),
            array(40, 10),
            array(45, 45),
            array(5, 45),
        );

        foreach (array('nonzero', 'evenodd') as $fillRule) {
            $rasterizer = new SVGRasterizer('50px', '50px', null, 50, 50);
            $obj->render($rasterizer, array(
                'points' => $points,
                'open' => false,
                'fill-rule' => $fillRule,
            ), $context);
            $img = $rasterizer->finish();

            $file = './tests/images/renderer-polygon-' . $fillRule . '.png';
            $this->assertThat($img, new GDSimilarityConstraint($file));
        }
    }
}
