<?php

namespace SVG\Rasterization\Renderers;

use SVG\Fonts\FontRegistry;
use SVG\Rasterization\Transform\Transform;

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
    protected function prepareRenderParams(array $options, Transform $transform, ?FontRegistry $fontRegistry): ?array
    {
        $x1 = $options['x1'];
        $y1 = $options['y1'];
        $transform->map($x1, $y1);

        $x2 = $options['x2'];
        $y2 = $options['y2'];
        $transform->map($x2, $y2);

        return [
            'x1' => $x1,
            'y1' => $y1,
            'x2' => $x2,
            'y2' => $y2,
        ];
    }

    /**
     * @inheritdoc
     */
    protected function renderFill($image, $params, int $color): void
    {
        // can't fill
    }

    /**
     * @inheritdoc
     */
    protected function renderStroke($image, $params, int $color, float $strokeWidth): void
    {
        imagesetthickness($image, round($strokeWidth));
        imageline($image, $params['x1'], $params['y1'], $params['x2'], $params['y2'], $color);
    }
}
