<?php

namespace SVG\Rasterization\Renderers;

use SVG\Rasterization\SVGRasterizer;

/**
 * This renderer can draw straight lines. Filling is not supported.
 *
 * Options:
 * - float x1: first x coordinate
 * - float y1: first y coordinate
 * - float x2: second x coordinate
 * - float y2: second y coordinate
 */
class LineRenderer extends MultiPassRenderer
{
    /**
     * @inheritdoc
     */
    protected function prepareRenderParams(SVGRasterizer $rasterizer, array $options)
    {
        $offsetX = $rasterizer->getOffsetX();
        $offsetY = $rasterizer->getOffsetY();

        return array(
            'x1' => $options['x1'] * $rasterizer->getScaleX() + $offsetX,
            'y1' => $options['y1'] * $rasterizer->getScaleY() + $offsetY,
            'x2' => $options['x2'] * $rasterizer->getScaleX() + $offsetX,
            'y2' => $options['y2'] * $rasterizer->getScaleY() + $offsetY,
        );
    }

    /**
     * @inheritdoc
     */
    protected function renderFill($image, array $params, $color)
    {
        // can't fill
    }

    /**
     * @inheritdoc
     */
    protected function renderStroke($image, array $params, $color, $strokeWidth)
    {
        imagesetthickness($image, round($strokeWidth));
        imageline($image, $params['x1'], $params['y1'], $params['x2'], $params['y2'], $color);
    }
}
