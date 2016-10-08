<?php

namespace JangoBrick\SVG\Rasterization\Renderers;

use JangoBrick\SVG\Rasterization\SVGRasterizer;

class SVGLineRenderer extends SVGRenderer
{
    protected function prepareRenderParams(SVGRasterizer $rasterizer, array $options)
    {
        return array(
            'x1' => $options['x1'] * $rasterizer->getScaleX(),
            'y1' => $options['y1'] * $rasterizer->getScaleY(),
            'x2' => $options['x2'] * $rasterizer->getScaleX(),
            'y2' => $options['y2'] * $rasterizer->getScaleY(),
        );
    }

    /**
     * @SuppressWarnings("unused")
     */
    protected function renderFill($image, array $params, $color)
    {
        // can't fill
    }

    protected function renderStroke($image, array $params, $color, $strokeWidth)
    {
        imagesetthickness($image, $strokeWidth);
        imageline($image, $params['x1'], $params['y1'], $params['x2'], $params['y2'], $color);
    }
}
