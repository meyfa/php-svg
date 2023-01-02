<?php

namespace SVG\Rasterization\Transform;

final class TransformParser
{
    /**
     * Convert a 'transform' attribute value into a Transform object by starting with the identity transform and
     * applying each of the operations specified in the input string.
     * Alternatively, if a Transform object already exists to which further operations should be added, it can be
     * passed as an argument. In that case, no new Transform object will be allocated, and the given one will be used
     * as the starting point instead.
     *
     * @param string|null    $input   The string to parse.
     * @param Transform|null $applyTo The optional starting Transform. If not provided, the identity will be used.
     * @return Transform Either the mutated argument transform, or the newly computed transform.
     */
    public static function parseTransformString(?string $input, Transform $applyTo = null): Transform
    {
        $transform = $applyTo ?? Transform::identity();
        if ($input == null) {
            return $transform;
        }

        // https://www.w3.org/TR/css-transforms-1/#svg-syntax

        $matches = [];
        preg_match_all(
            '/(translate|scale|rotate|skewX|skewY|matrix)\s*\(\s*([^)]+)\s*\)/',
            $input,
            $matches,
            PREG_SET_ORDER
        );

        foreach ($matches as $match) {
            $operation = $match[1];
            $arguments = self::splitArguments($match[2]);

            self::$operation($transform, $arguments);
        }

        return $transform;
    }

    private static function splitArguments(string $argumentString): array
    {
        $args = [];
        if ($argumentString !== '') {
            preg_match_all('/[+-]?(\d*\.\d+|\d+)(e[+-]?\d+)?/', $argumentString, $args);
            $args = $args[0];
        }

        return $args;
    }

    // the following functions are invoked dynamically

    /**
     * @SuppressWarnings("unused")
     * @noinspection PhpUnusedPrivateMethodInspection
     */
    private static function translate(Transform $transform, array $arguments): void
    {
        if (count($arguments) === 2) {
            $transform->translate((float) $arguments[0], (float) $arguments[1]);
        }
    }

    /**
     * @SuppressWarnings("unused")
     * @noinspection PhpUnusedPrivateMethodInspection
     */
    private static function scale(Transform $transform, array $arguments): void
    {
        if (count($arguments) === 2) {
            $transform->scale((float) $arguments[0], (float) $arguments[1]);
        }
    }

    /**
     * @SuppressWarnings("unused")
     * @noinspection PhpUnusedPrivateMethodInspection
     */
    private static function rotate(Transform $transform, array $arguments): void
    {
        if (count($arguments) === 1) {
            $transform->rotate(deg2rad((float) $arguments[0]));
        }
    }

    /**
     * @SuppressWarnings("unused")
     * @noinspection PhpUnusedPrivateMethodInspection
     */
    private static function skewX(Transform $transform, array $arguments): void
    {
        if (count($arguments) === 1) {
            $transform->skewX(deg2rad((float) $arguments[0]));
        }
    }

    /**
     * @SuppressWarnings("unused")
     * @noinspection PhpUnusedPrivateMethodInspection
     */
    private static function skewY(Transform $transform, array $arguments): void
    {
        if (count($arguments) === 1) {
            $transform->skewY(deg2rad((float) $arguments[0]));
        }
    }

    /**
     * @SuppressWarnings("unused")
     * @noinspection PhpUnusedPrivateMethodInspection
     */
    private static function matrix(Transform $transform, array $arguments): void
    {
        if (count($arguments) === 6) {
            $transform->multiply(new Transform(array_map('floatval', $arguments)));
        }
    }
}
