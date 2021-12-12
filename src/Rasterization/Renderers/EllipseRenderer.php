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
            'cx'        => self::prepareLengthX($options['cx'], $rasterizer) + $rasterizer->getOffsetX(),
            'cy'        => self::prepareLengthY($options['cy'], $rasterizer) + $rasterizer->getOffsetY(),
            'width'     => self::prepareLengthX($options['rx'], $rasterizer) * 2,
            'height'    => self::prepareLengthY($options['ry'], $rasterizer) * 2,
            'dash'      => [(float)$options['dash'][0] / $options['rx'] * $rasterizer->getDocumentWidth() / 2.01, (float)$options['dash'][1] / $options['rx'] * $rasterizer->getDocumentWidth() / 2.01],
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
     *
     */
    private function dashedcircle($im, $cx, $cy, $radius, $color, $dashstyle = [5, 2])
    {
        $dash = false;

        if ($dash) {
            $dashsize = $dashstyle[0];
        }
        else {
            $dashsize = $dashstyle[1];
        }

        // for ($angle = 0; $angle <= (180 + $dashsize); $angle += $dashsize) {
        for ($angle = 0; $angle <= (360 + $dashsize); $angle += $dashsize) {
            $x = ($radius * cos(deg2rad($angle)));
            $y = ($radius * sin(deg2rad($angle)));

            if ($dash) {
                imageline($im, $cx+$px, $cy+$py, $cx+$x, $cy+$y, $color);
                // imageline($im, $cx-$px, $cx-$py, $cx-$x, $cy-$y, $color);
            }

            $dash = !$dash;
            $px = $x;
            $py = $y;

            if ($dash) {
                $dashsize = $dashstyle[0];
            }
            else {
                $dashsize = $dashstyle[1];
            }
        }
    }

    /**
     * @inheritdoc
     */
    protected function renderStroke($image, array $params, $color, $strokeWidth)
    {
        imagesetthickness($image, $strokeWidth);

        $width = $params['width'];
        if ($width % 2 === 0) {
            $width += 1;
        }
        $height = $params['height'];
        if ($height % 2 === 0) {
            $height += 1;
        }

        // imageellipse ignores imagesetthickness; draw arc instead
        if ($params['dash'][0] == 0.0 || $params['dash'][1] == 0.0) {
            imagearc($image, $params['cx'], $params['cy'], $width, $height, 0, 360, $color);
        }
        else {
            if ($width == $height) {
                $this->dashedcircle($image, $params['cx'], $params['cy'], $width / 2, $color, $params['dash']);
            }
        }
    }
}
