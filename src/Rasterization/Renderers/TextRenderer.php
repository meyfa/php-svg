<?php

namespace SVG\Rasterization\Renderers;

use SVG\Rasterization\SVGRasterizer;

/**
 * This renderer can draw basic text.
 *
 * Options:
 * - float x: the x coordinate of the text
 * - float y: the y coordinate of the baseline
 * - string anchor: the anchor point (start|middle|end) for x coordinate
 * - float size: the font size
 * - string font_path: the path to the font file (.ttf)
 * - string text: the text to draw
 */
class TextRenderer extends MultiPassRenderer
{
    /**
     * @inheritdoc
     */
    protected function prepareRenderParams(SVGRasterizer $rasterizer, array $options)
    {
        $size = self::prepareLengthY($options['size'], $rasterizer);

        // text-anchor
        $anchorOffset = 0;
        if ($options['anchor'] === 'middle' || $options['anchor'] === 'end') {
            $width = self::calculateTextWidth($options['text'], $options['font_path'], $size);
            $anchorOffset = $options['anchor'] === 'middle' ? ($width / 2) : $width;
        }

        $x = self::prepareLengthX($options['x'], $rasterizer) + $rasterizer->getOffsetX();
        $y = self::prepareLengthY($options['y'], $rasterizer) + $rasterizer->getOffsetY();

        return array(
            'x'         => $x - $anchorOffset,
            'y'         => $y,
            'size'      => $size,
            'font_path' => $options['font_path'],
            'text'      => $options['text'],
        );
    }

    /**
     * @inheritdoc
     */
    protected function renderFill($image, array $params, $color)
    {
        imagettftext(
            $image,
            $params['size'],
            0,
            $params['x'],
            $params['y'],
            $color,
            $params['font_path'],
            $params['text']
        );
    }

    /**
     * @inheritdoc
     */
    protected function renderStroke($image, array $params, $color, $strokeWidth)
    {
        $x = $params['x'];
        $y = $params['y'];
        $px = $strokeWidth;

        for ($c1 = ($x - abs($px)); $c1 <= ($x + abs($px)); $c1++) {
            for ($c2 = ($y - abs($px)); $c2 <= ($y + abs($px)); $c2++) {
                imagettftext($image, $params['size'], 0, $c1, $c2, $color, $params['font_path'], $params['text']);
            }
        }
    }

    /**
     * Compute the width, in pixels, of the given text.
     *
     * @param string $text The text to measure.
     * @param string $fontFile The font file path.
     * @param float $size The font size in pixels.
     *
     * @return float The width in pixels.
     */
    private static function calculateTextWidth($text, $fontFile, $size)
    {
        // note for future: imagettfbbox is unable to calculate height properly.
        // width should be fine though.

        $box = imagettfbbox($size, 0, $fontFile, $text);

        $minX = min($box[0], $box[2], $box[4], $box[6]);
        $maxX = max($box[0], $box[2], $box[4], $box[6]);

        return abs($maxX - $minX);
    }
}
