<?php

namespace SVG\Reading;

/**
 * This converter detects unitless lengths and converts them to qualified
 * lengths by appending 'px' (e.g. '42' => '42px').
 *
 * This is required because SVG allows unitless presentation attributes, but not
 * unitless CSS properties
 * (e.g. `font-size="11"` is valid, but `style="font-size: 11"` is not).
 */
class LengthAttributeConverter implements AttributeConverter
{
    private static $instance;

    /**
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }

    /**
     * Obtain the instance of this class.
     *
     * @return self The singleton instance.
     *
     * @codeCoverageIgnore
     */
    public static function getInstance(): self
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * @inheritdoc
     */
    public function convert(string $value): string
    {
        if (preg_match('/^\s*([+-]?(?:\d+\.?|\d*\.?\d+))\s*$/', $value, $matches)) {
            return $matches[1] . 'px';
        }
        return $value;
    }
}
