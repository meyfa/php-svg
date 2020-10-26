<?php

namespace SVG\Reading;

/**
 * Stores information about possible attributes, notably whether they are
 * styles.
 */
class AttributeRegistry
{
    /**
     * @var string[] @styleAttributes Attributes to be interpreted as styles.
     * List comes from https://www.w3.org/TR/SVG/styling.html.
     */
    private static $styleAttributes = array(
        // DEFINED IN BOTH CSS2 AND SVG
        // font properties
        'font', 'font-family', 'font-size', 'font-size-adjust', 'font-stretch',
        'font-style', 'font-variant', 'font-weight',
        // text properties
        'direction', 'letter-spacing', 'word-spacing', 'text-decoration',
        'unicode-bidi',
        // other properties for visual media
        'clip', 'color', 'cursor', 'display', 'overflow', 'visibility',
        // NOT DEFINED IN CSS2
        // clipping, masking and compositing properties
        'clip-path', 'clip-rule', 'mask', 'opacity',
        // filter effects properties
        'enable-background', 'filter', 'flood-color', 'flood-opacity',
        'lighting-color',
        // gradient properties
        'stop-color', 'stop-opacity',
        // interactivity properties
        'pointer-events',
        // color and painting properties
        'color-interpolation', 'color-interpolation-filters', 'color-profile',
        'color-rendering', 'fill', 'fill-opacity', 'fill-rule',
        'image-rendering', 'marker', 'marker-end', 'marker-mid', 'marker-start',
        'shape-rendering', 'stroke', 'stroke-dasharray', 'stroke-dashoffset',
        'stroke-linecap', 'stroke-linejoin', 'stroke-miterlimit',
        'stroke-opacity', 'stroke-width', 'text-rendering',
        // text properties
        'alignment-base', 'baseline-shift', 'dominant-baseline',
        'glyph-orientation-horizontal', 'glyph-orientation-vertical', 'kerning',
        'text-anchor', 'writing-mode',
    );

    /**
     * @var string[] $styleConverters Map of style attributes to class names
     * for SVG attribute to CSS property conversion.
     */
    private static $styleConverters = array(
        'font-size'         => 'SVG\Reading\LengthAttributeConverter',
        'letter-spacing'    => 'SVG\Reading\LengthAttributeConverter',
        'word-spacing'      => 'SVG\Reading\LengthAttributeConverter',
    );

    /**
     * Check whether the given attribute name denotes a presentation attribute
     * that can exist as a CSS property.
     *
     * @param string $key The attribute name.
     *
     * @return boolean Whether the attribute is a style.
     */
    public static function isStyle($key)
    {
        return in_array($key, self::$styleAttributes);
    }

    /**
     * Some styles, notably font sizes / spacing, follow different syntactic
     * rules as attributes vs. as CSS properties. This function helps with
     * converting from an attribute value to a CSS property value.
     *
     * @param string $key   The attribute name.
     * @param string $value The attribute value.
     *
     * @return string The converted value for use in a CSS property.
     */
    public static function convertStyleAttribute($key, $value)
    {
        if (!isset(self::$styleConverters[$key])) {
            return $value;
        }
        $converter = call_user_func(array(self::$styleConverters[$key], 'getInstance'));
        return $converter->convert($value);
    }
}
