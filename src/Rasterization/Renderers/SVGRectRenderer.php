<?php

namespace JangoBrick\SVG\Rasterization\Renderers;

use JangoBrick\SVG\Rasterization\SVGRasterizer;

/**
 * This renderer can draw rectangles.
 *
 * Options:
 * - float x: the x coordinate of the upper left corner
 * - float y: the y coordinate of the upper left corner
 * - float width: the width
 * - float height: the height
 */
class SVGRectRenderer extends SVGRenderer
{
    protected function prepareRenderParams(SVGRasterizer $rasterizer, array $options)
    {
        $x1 = self::prepareLengthX($options['x'], $rasterizer);
        $y1 = self::prepareLengthY($options['y'], $rasterizer);
        $w  = self::prepareLengthX($options['width'], $rasterizer);
        $h  = self::prepareLengthY($options['height'], $rasterizer);

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
        // and drawing 4 lines instead doesn't work either because of
        // unpredictable positioning as well as overlaps,
        // so we draw four filled rectangles instead

        $halfStrokeFloor = floor($strokeWidth / 2);
        $halfStrokeCeil  = ceil($strokeWidth / 2);

        // top
        imagefilledrectangle(
            $image,
            $x1 - $halfStrokeFloor,     $y1 - $halfStrokeFloor,
            $x2 + $halfStrokeFloor,     $y1 + $halfStrokeCeil - 1,
            $color
        );
        // bottom
        imagefilledrectangle(
            $image,
            $x1 - $halfStrokeFloor,     $y2 - $halfStrokeCeil + 1,
            $x2 + $halfStrokeFloor,     $y2 + $halfStrokeFloor,
            $color
        );
        // left
        imagefilledrectangle(
            $image,
            $x1 - $halfStrokeFloor,     $y1 + $halfStrokeCeil,
            $x1 + $halfStrokeCeil - 1,  $y2 - $halfStrokeCeil,
            $color
        );
        // right
        imagefilledrectangle(
            $image,
            $x2 - $halfStrokeCeil + 1,  $y1 + $halfStrokeCeil,
            $x2 + $halfStrokeFloor,     $y2 - $halfStrokeCeil,
            $color
        );
    }
}
