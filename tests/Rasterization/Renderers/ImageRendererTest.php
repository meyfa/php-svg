<?php

namespace SVG;

use AssertGD\GDSimilarityConstraint;
use SVG\Rasterization\Renderers\ImageRenderer;
use SVG\Nodes\SVGNode;
use SVG\Rasterization\SVGRasterizer;

class SVGNodeClass extends SVGNode
{
    const TAG_NAME = 'test_subclass';

    /**
     * @inheritdoc
     */
    public function rasterize(\SVG\Rasterization\SVGRasterizer $rasterizer): void
    {
    }
}

/**
 * @requires extension gd
 * @covers \SVG\Rasterization\Renderers\ImageRenderer
 *
 * @SuppressWarnings(PHPMD)
 */
class ImageRendererTest extends \PHPUnit\Framework\TestCase
{
    public function testRender()
    {
        $obj = new ImageRenderer();

        $context = new SVGNodeClass();

        $rasterizer = new SVGRasterizer(10, 10, null, 100, 100);
        $obj->render($rasterizer, [
            'href'   => __DIR__ . '/../../squares.svg',
            'x'      => 1,
            'y'      => 2,
            'width'  => 6,
            'height' => 6
        ], $context);
        $img = $rasterizer->finish();

        $this->assertThat($img, new GDSimilarityConstraint('./tests/images/renderer-image.png'));
    }

    public function testDefaultsXAndYToZero()
    {
        $obj = new ImageRenderer();

        $context = new SVGNodeClass();

        $rasterizer = new SVGRasterizer('10px', '10px', null, 100, 100);
        $obj->render($rasterizer, [
            'href'   => __DIR__ . '/../../squares.svg',
            'x'      => null,
            'y'      => null,
            'width'  => 6,
            'height' => 6
        ], $context);
        $img = $rasterizer->finish();

        $this->assertThat($img, new GDSimilarityConstraint('./tests/images/renderer-image-x0y0.png'));
    }
}
