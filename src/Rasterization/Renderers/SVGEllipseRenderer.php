<?php

namespace JangoBrick\SVG\Rasterization\Renderers;

use JangoBrick\SVG\Rasterization\SVGRasterizer;

class SVGEllipseRenderer extends SVGRenderer
{
    protected function prepareRenderParams(SVGRasterizer $rasterizer, array $options)
    {
        return array(
            'cx'        => $options['cx'] * $rasterizer->getScaleX(),
            'cy'        => $options['cy'] * $rasterizer->getScaleY(),
            'width'     => $options['rx'] * 2 * $rasterizer->getScaleX(),
            'height'    => $options['ry'] * 2 * $rasterizer->getScaleY(),
        );
    }

    protected function renderFill($image, array $params, $color)
    {
        imagefilledellipse($image, $params['cx'], $params['cy'], $params['width'], $params['height'], $color);
    }

    protected function renderStroke($image, array $params, $color, $strokeWidth)
    {
        imagesetthickness($image, $strokeWidth);

        $width = $params['width'];
        if (intval($width) % 2 === 0) {
            $width += 1;
        }
        $height = $params['height'];
        if (intval($height) % 2 === 0) {
            $height += 1;
        }

        // imageellipse ignores imagesetthickness; draw arc instead
        imagearc($image, $params['cx'], $params['cy'], $width, $height, 0, 360, $color);
    }
}
