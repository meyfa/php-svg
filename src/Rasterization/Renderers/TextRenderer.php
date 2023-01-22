<?php

namespace SVG\Rasterization\Renderers;

use SVG\Fonts\FontRegistry;
use SVG\Rasterization\Transform\Transform;

/**
 * This renderer can draw basic text.
 *
 * Options:
 * - float x: the x coordinate of the text
 * - float y: the y coordinate of the baseline
 * - string anchor: the anchor point (start|middle|end) for x coordinate
 * - float fontSize: the font size
 * - string fontFamily: the font family
 * - string fontStyle: the font style (normal|italic|oblique)
 * - string fontWeight: the font weight (normal|bold|bolder|lighter|(number))
 * - string text: the text to draw
 */
class TextRenderer extends MultiPassRenderer
{
    /**
     * @inheritdoc
     */
    protected function prepareRenderParams(array $options, Transform $transform, ?FontRegistry $fontRegistry): ?array
    {
        // this assumes there is no rotation or skew, but that's fine, we can't deal with that anyway
        $size1 = $options['fontSize'];
        $size2 = $size1;
        $transform->resize($size1, $size2);
        $size = min($size1, $size2);

        $fontPath = null;
        if (isset($fontRegistry)) {
            $isItalic = $options['fontStyle'] === 'italic' || $options['fontStyle'] === 'oblique';
            $weight = self::resolveFontWeight($options['fontWeight']);
            $matchingFont = $fontRegistry->findMatchingFont($options['fontFamily'], $isItalic, $weight);
            if ($matchingFont !== null) {
                $fontPath = $matchingFont->getPath();
            }
        }

        if (!isset($fontPath)) {
            return null;
        }

        // text-anchor
        $anchorOffset = 0;
        if ($options['anchor'] === 'middle' || $options['anchor'] === 'end') {
            $width = self::calculateTextWidth($options['text'], $fontPath, $size);
            $anchorOffset = $options['anchor'] === 'middle' ? ($width / 2) : $width;
        }

        $x = $options['x'];
        $y = $options['y'];
        $transform->map($x, $y);

        return [
            'x'         => $x - $anchorOffset,
            'y'         => $y,
            'size'      => $size,
            'fontPath'  => $fontPath,
            'text'      => $options['text'],
        ];
    }

    /**
     * @inheritdoc
     */
    protected function renderFill($image, $params, int $color): void
    {
        imagettftext(
            $image,
            $params['size'],
            0,
            $params['x'],
            $params['y'],
            $color,
            $params['fontPath'],
            $params['text']
        );
    }

    /**
     * @inheritdoc
     */
    protected function renderStroke($image, $params, int $color, float $strokeWidth): void
    {
        $x = $params['x'];
        $y = $params['y'];
        $px = $strokeWidth;

        for ($c1 = ($x - abs($px)); $c1 <= ($x + abs($px)); $c1++) {
            for ($c2 = ($y - abs($px)); $c2 <= ($y + abs($px)); $c2++) {
                imagettftext($image, $params['size'], 0, $c1, $c2, $color, $params['fontPath'], $params['text']);
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
    private static function calculateTextWidth(string $text, string $fontFile, float $size): float
    {
        // note for future: imagettfbbox is unable to calculate height properly.
        // width should be fine though.

        $box = imagettfbbox($size, 0, $fontFile, $text);

        $minX = min($box[0], $box[2], $box[4], $box[6]);
        $maxX = max($box[0], $box[2], $box[4], $box[6]);

        return abs($maxX - $minX);
    }

    private static function resolveFontWeight($weight): int
    {
        // TODO implement "bolder" and "lighter"

        if (is_numeric($weight)) {
            return (int) $weight;
        } elseif ($weight === 'bold') {
            return 700;
        }

        return 400;
    }
}
