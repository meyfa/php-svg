<?php

namespace SVG\Rasterization\Renderers;

use SVG\Rasterization\Transform\Transform;

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
    protected function prepareRenderParams(array $options, Transform $transform)
    {
        $cx = $options['cx'];
        $cy = $options['cy'];
        $transform->map($cx, $cy);

        $width = $options['rx'] * 2;
        $height = $options['ry'] * 2;
        $transform->resize($width, $height);

        return [
            'cx'        => $cx,
            'cy'        => $cy,
            'width'     => $width,
            'height'    => $height,
        ];
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
