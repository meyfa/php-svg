<?php

namespace JangoBrick\SVG\Rasterization\Renderers;

use JangoBrick\SVG\Rasterization\SVGRasterizer;

/**
 * This renderer can draw straight lines. Filling is not supported.
 *
 * Options:
 * - float x1: first x coordinate
 * - float y1: first y coordinate
 * - float x2: second x coordinate
 * - float y2: second y coordinate
 */
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
