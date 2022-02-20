<?php

namespace SVG\Rasterization\Renderers;

use SVG\Rasterization\SVGRasterizer;

/**
 * This renderer can draw ellipses (and circles).
 *
 * Options:
 * - float cx: x coordinate of center point
 * - float cy: y coordinate of center point
 * - float rx: radius along x-axis
 * - float ry: radius along y-axis
 */
class EllipseRenderer extends MultiPassRenderer
{
    /**
     * @inheritdoc
     */
    protected function prepareRenderParams(SVGRasterizer $rasterizer, array $options)
    {
        return array(
            'cx'        => $options['cx'] * $rasterizer->getScaleX() + $rasterizer->getOffsetX(),
            'cy'        => $options['cy'] * $rasterizer->getScaleY() + $rasterizer->getOffsetY(),
            'width'     => $options['rx'] * $rasterizer->getScaleX() * 2,
            'height'    => $options['ry'] * $rasterizer->getScaleY() * 2,
        );
    }

    /**
     * @inheritdoc
     */
    protected function renderFill($image, array $params, $color)
    {
        imagefilledellipse($image, $params['cx'], $params['cy'], $params['width'], $params['height'], $color);
    }

    /**
     * @inheritdoc
     */
    protected function renderStroke($image, array $params, $color, $strokeWidth)
    {
        imagesetthickness($image, round($strokeWidth));

        $width = $params['width'];
        if ($width % 2 === 0) {
            $width += 1;
        }
        $height = $params['height'];
        if ($height % 2 === 0) {
            $height += 1;
        }

        // imageellipse ignores imagesetthickness; draw arc instead
        imagearc($image, $params['cx'], $params['cy'], $width, $height, 0, 360, $color);
    }
}
