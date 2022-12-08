<?php

namespace SVG;

use AssertGD\GDSimilarityConstraint;
use SVG\Rasterization\SVGRasterizer;
use SVG\Rasterization\Renderers\RectRenderer;

/**
 * @requires extension gd
 * @covers \SVG\Rasterization\Renderers\RectRenderer
 *
 * @SuppressWarnings(PHPMD)
 */
class RectRendererTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldRenderStroke()
    {
        $obj = new RectRenderer();

        $context = $this->getMockForAbstractClass('\SVG\Nodes\SVGNode');
        $context->setStyle('fill', 'none');
        $context->setStyle('stroke', '#FF0000');
        $context->setStyle('stroke-width', '1px');

        $rasterizer = new SVGRasterizer('40px', '40px', null, 40, 40);
        $obj->render($rasterizer, [
            'x' => 4, 'y' => 8,
            'width' => 20, 'height' => 16,
        ], $context);
        $img = $rasterizer->finish();

        $this->assertThat($img, new GDSimilarityConstraint('./tests/images/renderer-rect-stroke.png'));
    }

    public function testShouldRenderStrokeThick()
    {
        $obj = new RectRenderer();

        $context = $this->getMockForAbstractClass('\SVG\Nodes\SVGNode');
        $context->setStyle('fill', 'none');
        $context->setStyle('stroke', '#FF0000');
        $context->setStyle('stroke-width', '3px');

        $rasterizer = new SVGRasterizer('20px', '20px', null, 40, 40);
        $obj->render($rasterizer, [
            'x' => 2, 'y' => 4,
            'width' => 10, 'height' => 8,
        ], $context);
        $img = $rasterizer->finish();

        $this->assertThat($img, new GDSimilarityConstraint('./tests/images/renderer-rect-stroke-thick.png'));
    }

    public function testShouldRenderStrokeAlpha()
    {
        $obj = new RectRenderer();

        $context = $this->getMockForAbstractClass('\SVG\Nodes\SVGNode');
        $context->setStyle('fill', 'none');
        $context->setStyle('stroke', 'rgba(255, 0, 0, 0.5)');
        $context->setStyle('stroke-width', '3px');

        $rasterizer = new SVGRasterizer('20px', '20px', null, 40, 40);
        $obj->render($rasterizer, [
            'x' => 2, 'y' => 4,
            'width' => 10, 'height' => 8,
        ], $context);
        $img = $rasterizer->finish();

        $this->assertThat($img, new GDSimilarityConstraint('./tests/images/renderer-rect-stroke-alpha.png'));
    }

    public function testShouldRenderFill()
    {
        $obj = new RectRenderer();

        $context = $this->getMockForAbstractClass('\SVG\Nodes\SVGNode');
        $context->setStyle('fill', '#FF0000');
        $context->setStyle('stroke', 'none');

        $rasterizer = new SVGRasterizer('20px', '20px', null, 40, 40);
        $obj->render($rasterizer, [
            'x' => 2, 'y' => 4,
            'width' => 10, 'height' => 8,
        ], $context);
        $img = $rasterizer->finish();

        $this->assertThat($img, new GDSimilarityConstraint('./tests/images/renderer-rect-fill.png'));
    }

    public function testShouldRenderFillAlpha()
    {
        $obj = new RectRenderer();

        $context = $this->getMockForAbstractClass('\SVG\Nodes\SVGNode');
        $context->setStyle('fill', 'rgba(255, 0, 0, 0.5)');
        $context->setStyle('stroke', 'none');

        $rasterizer = new SVGRasterizer('20px', '20px', null, 40, 40);
        $obj->render($rasterizer, [
            'x' => 2, 'y' => 4,
            'width' => 10, 'height' => 8,
        ], $context);
        $img = $rasterizer->finish();

        $this->assertThat($img, new GDSimilarityConstraint('./tests/images/renderer-rect-fill-alpha.png'));
    }

    public function testShouldRenderStrokeAndFill()
    {
        $obj = new RectRenderer();

        $context = $this->getMockForAbstractClass('\SVG\Nodes\SVGNode');
        $context->setStyle('fill', 'rgba(255, 255, 255, 0.5)');
        $context->setStyle('stroke', 'rgba(0, 0, 0, 0.5)');
        $context->setStyle('stroke-width', '5px');

        $rasterizer = new SVGRasterizer('40px', '40px', null, 40, 40);
        $obj->render($rasterizer, [
            'x' => 4, 'y' => 8,
            'width' => 20, 'height' => 16,
        ], $context);
        $img = $rasterizer->finish();

        $this->assertThat($img, new GDSimilarityConstraint('./tests/images/renderer-rect-stroke-fill.png'));
    }

    public function testShouldRenderStrokeRounded()
    {
        $obj = new RectRenderer();

        $context = $this->getMockForAbstractClass('\SVG\Nodes\SVGNode');
        $context->setStyle('fill', 'none');
        $context->setStyle('stroke', '#FF0000');
        $context->setStyle('stroke-width', '1px');

        $rasterizer = new SVGRasterizer('40px', '40px', null, 40, 40);
        $obj->render($rasterizer, [
            'x' => 4, 'y' => 8,
            'width' => 20, 'height' => 16,
            'rx' => 4, 'ry' => 4,
        ], $context);
        $img = $rasterizer->finish();

        $this->assertThat($img, new GDSimilarityConstraint('./tests/images/renderer-rect-stroke-rounded.png'));
    }

    public function testShouldRenderFillRounded()
    {
        $obj = new RectRenderer();

        $context = $this->getMockForAbstractClass('\SVG\Nodes\SVGNode');
        $context->setStyle('fill', '#FF0000');
        $context->setStyle('stroke', 'none');

        $rasterizer = new SVGRasterizer('40px', '40px', null, 40, 40);
        $obj->render($rasterizer, [
            'x' => 4, 'y' => 8,
            'width' => 20, 'height' => 16,
            'rx' => 4, 'ry' => 4,
        ], $context);
        $img = $rasterizer->finish();

        $this->assertThat($img, new GDSimilarityConstraint('./tests/images/renderer-rect-fill-rounded.png'));
    }

    public function testDoesNotRenderIfWidthZero()
    {
        $obj = new RectRenderer();

        $context = $this->getMockForAbstractClass('\SVG\Nodes\SVGNode');
        $context->setStyle('fill', '#FF0000');
        $context->setStyle('stroke', '#0000FF');
        $context->setStyle('stroke-width', '1px');

        $rasterizer = new SVGRasterizer('40px', '40px', null, 40, 40);
        $obj->render($rasterizer, [
            'x' => 4, 'y' => 8,
            'width' => 0, 'height' => 16,
            'rx' => 4, 'ry' => 4,
        ], $context);
        $img = $rasterizer->finish();

        $this->assertThat($img, new GDSimilarityConstraint('./tests/images/renderer-rect-empty.png'));
    }

    public function testDoesNotRenderIfHeightZero()
    {
        $obj = new RectRenderer();

        $context = $this->getMockForAbstractClass('\SVG\Nodes\SVGNode');
        $context->setStyle('fill', '#FF0000');
        $context->setStyle('stroke', '#0000FF');
        $context->setStyle('stroke-width', '1px');

        $rasterizer = new SVGRasterizer('40px', '40px', null, 40, 40);
        $obj->render($rasterizer, [
            'x' => 4, 'y' => 8,
            'width' => 20, 'height' => 0,
            'rx' => 4, 'ry' => 4,
        ], $context);
        $img = $rasterizer->finish();

        $this->assertThat($img, new GDSimilarityConstraint('./tests/images/renderer-rect-empty.png'));
    }
}
