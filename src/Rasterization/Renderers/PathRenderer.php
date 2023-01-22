<?php

namespace SVG\Rasterization\Renderers;

use SVG\Fonts\FontRegistry;
use SVG\Rasterization\Path\PathApproximator;
use SVG\Rasterization\Transform\Transform;

/**
 * This renderer can draw arbitrary paths. It expects the paths to be given as the command format as returned by
 * PathParser. That format consists of an outer array containing one entry per command, with each such entry comprised
 * of an 'id' property and an 'args' property, which is itself an array of numbers. During render, these commands will
 * be approximated into polygonal subpaths.
 *
 * Options:
 * - array[] commands: The path commands, each containing an id string and an args array.
 * - string fill-rule: Either 'evenodd' or 'nonzero'. Defaults to 'nonzero'.
 */
class PathRenderer extends MultiPassRenderer
{
    /**
     * @inheritdoc
     */
    protected function prepareRenderParams(array $options, Transform $transform, ?FontRegistry $fontRegistry): ?array
    {
        $approximator = new PathApproximator($transform);
        $approximator->approximate($options['commands']);

        $subpaths = [];
        foreach ($approximator->getSubpaths() as $subpath) {
            $points = [];
            foreach ($subpath as $point) {
                $points[] = $point[0];
                $points[] = $point[1];
            }
            $subpaths[] = $points;
        }

        return [
            'subpaths'  => $subpaths,
            'fill-rule' => $options['fill-rule'],
        ];
    }

    /**
     * @inheritdoc
     */
    protected function renderFill($image, $params, int $color): void
    {
        PathRendererImplementation::fillMultipath($image, $params['subpaths'], $color, $params['fill-rule']);
    }

    /**
     * @inheritdoc
     */
    protected function renderStroke($image, $params, int $color, float $strokeWidth): void
    {
        foreach ($params['subpaths'] as $points) {
            PathRendererImplementation::strokeOpenSubpath($image, $points, $color, $strokeWidth);
        }
    }
}
