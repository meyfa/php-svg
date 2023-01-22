<?php

namespace SVG\Rasterization\Renderers;

use SVG\Fonts\FontRegistry;
use SVG\Rasterization\Transform\Transform;

/**
 * This renderer can draw polygons and polylines.
 * The points are provided as arrays with 2 entries: 0 => x coord, 1 => y coord.
 *
 * Options:
 * - bool open: if true, leaves first and last point disconnected (-> polyline)
 * - array[] points: array of coordinate tuples (i.e., array of array of float)
 * - string fill-rule: Either 'evenodd' or 'nonzero'. Defaults to 'nonzero'.
 */
class PolygonRenderer extends MultiPassRenderer
{
    /**
     * @inheritdoc
     */
    protected function prepareRenderParams(array $options, Transform $transform, ?FontRegistry $fontRegistry): ?array
    {
        $points = [];
        foreach ($options['points'] as $point) {
            $transform->mapInto($point[0], $point[1], $points);
        }

        return [
            'open'      => $options['open'] ?? false,
            'points'    => $points,
            'fill-rule' => $options['fill-rule'],
        ];
    }

    /**
     * @inheritdoc
     */
    protected function renderFill($image, $params, int $color): void
    {
        // Filling a polygon is equivalent to filling a path containing just a single polygonal subpath.
        PathRendererImplementation::fillMultipath($image, [$params['points']], $color, $params['fill-rule']);
    }

    /**
     * @inheritdoc
     */
    protected function renderStroke($image, $params, int $color, float $strokeWidth): void
    {
        if ($params['open']) {
            PathRendererImplementation::strokeOpenSubpath($image, $params['points'], $color, $strokeWidth);
            return;
        }
        PathRendererImplementation::strokeClosedSubpath($image, $params['points'], $color, $strokeWidth);
    }
}
