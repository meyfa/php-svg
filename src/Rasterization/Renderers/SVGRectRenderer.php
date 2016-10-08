<?php

namespace JangoBrick\SVG\Rasterization\Renderers;

use JangoBrick\SVG\Rasterization\SVGRasterizer;

class SVGRectRenderer extends SVGRenderer
{
    protected function prepareRenderParams(SVGRasterizer $rasterizer, array $options)
    {
        $x1 = $options['x'] * $rasterizer->getScaleX();
        $y1 = $options['y'] * $rasterizer->getScaleY();
        $w  = $options['width'] * $rasterizer->getScaleX();
        $h  = $options['height'] * $rasterizer->getScaleY();

        return array(
            'x1' => $x1,
            'y1' => $y1,
            'x2' => $x1 + $w - 1,
            'y2' => $y1 + $h - 1,
        );
    }

    protected function renderFill($image, array $params, $color)
    {
        imagefilledrectangle(
            $image,
            $params['x1'], $params['y1'],
            $params['x2'], $params['y2'],
            $color
        );
    }

    protected function renderStroke($image, array $params, $color, $strokeWidth)
    {
        imagesetthickness($image, $strokeWidth);

        $x1 = $params['x1'];
        $y1 = $params['y1'];
        $x2 = $params['x2'];
        $y2 = $params['y2'];

        // imagerectangle draws left and right side 1px thicker than it should,
        // so we draw four lines instead

        // TODO: remove overlaps, so that transparency works better

        imageline($image, $x1, $y1, $x2, $y1, $color); // top
        imageline($image, $x1, $y2, $x2, $y2, $color); // bottom
        imageline($image, $x1, $y1, $x1, $y2, $color); // left
        imageline($image, $x2, $y1, $x2, $y2, $color); // right
    }
}
