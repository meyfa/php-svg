<?php

namespace SVG\Rasterization\Renderers;

use SVG\Rasterization\SVGRasterizer;

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
        $x1 = self::prepareLengthX($options['x'], $rasterizer) + $rasterizer->getOffsetX();
        $y1 = self::prepareLengthY($options['y'], $rasterizer) + $rasterizer->getOffsetY();
        $w  = self::prepareLengthX($options['width'], $rasterizer);
        $h  = self::prepareLengthY($options['height'], $rasterizer);
        $rx = self::prepareLengthX($options['rx'], $rasterizer);
        $ry = self::prepareLengthY($options['ry'], $rasterizer);

        return array(
            'x1' => $x1,
            'y1' => $y1,
            'x2' => $x1 + $w - 1,
            'y2' => $y1 + $h - 1,
            'rx' => $rx - 1,
            'ry' => $ry - 1,
        );
    }

    protected function renderFill($image, array $params, $color)
    {
        $x1 = $params['x1'];
        $y1 = $params['y1'];
        $x2 = $params['x2'];
        $y2 = $params['y2'];
        $rx = $params['rx'];
        $ry = $params['ry'];

        if (($rx !== 0) || ($ry !== 0)) {
            self::renderFillRounded($image, $params, $color);
            return ;
        }
        imagefilledrectangle(
            $image,
            $x1, $y1,
            $x2, $y2,
            $color
        );
    }

    private function renderFillRounded($image, array $params, $color) {
        $x1 = $params['x1'];
        $y1 = $params['y1'];
        $x2 = $params['x2'];
        $y2 = $params['y2'];
        $rx = $params['rx'];
        $ry = $params['ry'];

        imagefilledrectangle(
            $image,
            $x1 + $rx, $y1,
            $x2 - $rx, $y2,
            $color
        );
        imagefilledrectangle(
            $image,
            $x1, $y1 + $ry,
            $x2, $y2 - $ry,
            $color
        );
        
        // left-top
        imagefilledellipse(
            $image,
            $x1 + $rx, $y1 + $ry,
            $rx*2, $ry*2,
            $color);
        // right-top
        imagefilledellipse(
            $image,
            $x2 - $rx, $y1 + $ry,
            $rx*2, $ry*2,
            $color);
        // left-bottom
        imagefilledellipse(
            $image,
            $x1 + $rx, $y2 - $ry,
            $rx*2, $ry*2,
            $color);
        // right-bottom
        imagefilledellipse(
            $image,
            $x2 - $rx, $y2 - $ry,
            $rx*2, $ry*2,
            $color);
    }
    
    protected function renderStroke($image, array $params, $color, $strokeWidth)
    {
        imagesetthickness($image, $strokeWidth);

        $x1 = $params['x1'];
        $y1 = $params['y1'];
        $x2 = $params['x2'];
        $y2 = $params['y2'];
        $rx = $params['rx'];
        $ry = $params['ry'];

        // imagerectangle draws left and right side 1px thicker than it should,
        // and drawing 4 lines instead doesn't work either because of
        // unpredictable positioning as well as overlaps,
        // so we draw four filled rectangles instead

        $halfStrokeFloor = floor($strokeWidth / 2);
        $halfStrokeCeil  = ceil($strokeWidth / 2);

        if (($rx !== 0) || ($ry !== 0)) {
            self::renderStrokeRounded($image, $params, $color, $strokeWidth);
            return ;
        }
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

    private function renderStrokeRounded($image, array $params, $color, $strokeWidth) {
        $x1 = $params['x1'];
        $y1 = $params['y1'];
        $x2 = $params['x2'];
        $y2 = $params['y2'];
        $rx = $params['rx'];
        $ry = $params['ry'];

        $halfStrokeFloor = floor($strokeWidth / 2);
        $halfStrokeCeil  = ceil($strokeWidth / 2);

        // top
        imagefilledrectangle(
            $image,
            $x1 - $halfStrokeFloor + $rx*1.5,  $y1 - $halfStrokeFloor,
            $x2 + $halfStrokeFloor - $rx*1.5,  $y1 + $halfStrokeCeil - 1,
            $color
        );
        // bottom
        imagefilledrectangle(
            $image,
            $x1 - $halfStrokeFloor + $rx*1.5,  $y2 - $halfStrokeCeil + 1,
            $x2 + $halfStrokeFloor - $rx*1.5,  $y2 + $halfStrokeFloor,
            $color
        );
        // left
        imagefilledrectangle(
            $image,
            $x1 - $halfStrokeFloor,     $y1 + $halfStrokeCeil + $ry/2,
            $x1 + $halfStrokeCeil - 1,  $y2 - $halfStrokeCeil - $ry/2,
            $color
        );
        // right
        imagefilledrectangle(
            $image,
            $x2 - $halfStrokeCeil + 1,  $y1 + $halfStrokeCeil + $ry/2,
            $x2 + $halfStrokeFloor,     $y2 - $halfStrokeCeil - $ry/2,
            $color
        );
        imagesetthickness($image, 1.5);
        for ($sw = $strokeWidth  ; $sw >= -$halfStrokeCeil; $sw --) {
            // left-top
            imagearc(
                $image,
                $x1 + $rx, $y1 + $ry,
                $rx*2+$sw, $ry*2+$sw,
                180, 270,
                $color);
            // right-top
            imagearc(
                $image,
                $x2 - $rx, $y1 + $ry,
                $rx*2+$sw, $ry*2+$sw,
                270, 360,
                $color);
            // left-bottom
            imagearc(
                $image,
                $x1 + $rx, $y2 - $ry,
                $rx*2+$sw, $ry*2+$sw,
                90,180,
                $color);
            // right-bottom
            imagearc(
                $image,
                $x2 - $rx, $y2 - $ry,
                $rx*2+$sw, $ry*2+$sw,
                0, 90,
                $color);
        }
    }
}
