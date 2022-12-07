<?php

namespace SVG\Rasterization\Renderers;

use SVG\Rasterization\Path\ArcApproximator;
use SVG\Rasterization\Transform\Transform;

/**
 * This renderer can draw rectangles.
 *
 * Options:
 * - float x: the x coordinate of the upper left corner
 * - float y: the y coordinate of the upper left corner
 * - float width: the width
 * - float height: the height
 * - float rx: the x radius of the corners.
 * - float ry: the y radius of the corners.
 */
class RectRenderer extends PolygonRenderer
{
    private static $arc;

    /**
     * @inheritdoc
     */
    protected function prepareRenderParams(array $options, Transform $transform)
    {
        $w = $options['width'];
        $h = $options['height'];

        if ($w <= 0 || $h <= 0) {
            return array(
                'open'      => false,
                'points'    => array(),
                'fill-rule' => 'nonzero',
            );
        }

        // Corner radii may at most be (width-1)/2 pixels long.
        // Anything larger than that and the circles start expanding beyond the rectangle.
        $rx = empty($options['rx']) ? 0 : $options['rx'];
        if ($rx > ($w - 1) / 2) {
            $rx = floor(($w - 1) / 2);
        }
        if ($rx < 0) {
            $rx = 0;
        }
        $ry = empty($options['ry']) ? 0 : $options['ry'];
        if ($ry > ($h - 1) / 2) {
            $ry = floor(($h - 1) / 2);
        }
        if ($ry < 0) {
            $ry = 0;
        }

        $x1 = $options['x'];
        $y1 = $options['y'];

        $points = $rx > 0 && $ry > 0
            ? self::getPointsForRoundedRect($x1, $y1, $w, $h, $rx, $ry, $transform)
            : self::getPointsForRect($x1, $y1, $w, $h, $transform);

        return array(
            'open'      => false,
            'points'    => $points,
            'fill-rule' => 'nonzero',
        );
    }

    private static function getPointsForRect($x1, $y1, $width, $height, Transform $transform)
    {
        $points = array();

        $transform->mapInto($x1, $y1, $points);
        $transform->mapInto($x1 + $width, $y1, $points);
        $transform->mapInto($x1 + $width, $y1 + $height, $points);
        $transform->mapInto($x1, $y1 + $height, $points);

        return $points;
    }

    private static function getPointsForRoundedRect($x1, $y1, $width, $height, $rx, $ry, Transform $transform)
    {
        if (!isset(self::$arc)) {
            self::$arc = new ArcApproximator();
        }

        // guess a scale factor
        $scaledRx = $rx;
        $scaledRy = $ry;
        $transform->resize($scaledRx, $scaledRy);
        $scale = $rx == 0 || $ry == 0 ? 1.0 : hypot($scaledRx / $rx, $scaledRy / $ry);

        $points = array();

        $topLeft = self::$arc->approximate(
            array($x1, $y1 + $ry),
            array($x1 + $rx, $y1),
            false,
            true,
            $rx,
            $ry,
            0,
            $scale
        );
        foreach ($topLeft as $point) {
            $transform->mapInto($point[0], $point[1], $points);
        }

        $topRight = self::$arc->approximate(
            array($x1 + $width - $rx, $y1),
            array($x1 + $width, $y1 + $ry),
            false,
            true,
            $rx,
            $ry,
            0,
            $scale
        );
        foreach ($topRight as $point) {
            $transform->mapInto($point[0], $point[1], $points);
        }

        $bottomRight = self::$arc->approximate(
            array($x1 + $width, $y1 + $height - $ry),
            array($x1 + $width - $rx, $y1 + $height),
            false,
            true,
            $rx,
            $ry,
            0,
            $scale
        );
        foreach ($bottomRight as $point) {
            $transform->mapInto($point[0], $point[1], $points);
        }

        $bottomLeft = self::$arc->approximate(
            array($x1 + $rx, $y1 + $height),
            array($x1, $y1 + $height - $ry),
            false,
            true,
            $rx,
            $ry,
            0,
            $scale
        );
        foreach ($bottomLeft as $point) {
            $transform->mapInto($point[0], $point[1], $points);
        }

        return $points;
    }
}
