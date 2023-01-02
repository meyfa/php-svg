<?php

namespace SVG\Rasterization\Path;

/**
 * This class can approximate elliptical arc segments by calculating a series of
 * points on them (converting them to polylines).
 */
class ArcApproximator
{
    private static $EPSILON = 0.0000001;

    /**
     * Approximates an elliptical arc segment given the start point, the end
     * point, the section to use (large or small), the sweep direction,
     * the ellipse's radii, and its rotation.
     *
     * All of the points (input and output) are represented as float arrays
     * where [0 => x coordinate, 1 => y coordinate].
     *
     * The image scale can be given for somewhat better approximation. For example, when the image coordinate space
     * is 4 times larger than path coordinate space, the scale should be 4. The result in that case will be that
     * 4 times as many points are generated.
     *
     * @param float[] $start    The start point (x0, y0).
     * @param float[] $end      The end point (x1, y1).
     * @param bool    $large    The large arc flag.
     * @param bool    $sweep    The sweep direction flag.
     * @param float   $radiusX  The x radius.
     * @param float   $radiusY  The y radius.
     * @param float   $rotation The x-axis angle / the ellipse's rotation (radians).
     * @param float   $scale    The scale factor to go from path coordinate space to image space.
     *
     * @return array[] An approximation for the curve, as an array of points.
     */
    public function approximate(
        array $start,
        array $end,
        bool $large,
        bool $sweep,
        float $radiusX,
        float $radiusY,
        float $rotation,
        float $scale = 1.0
    ): array {
        // out-of-range parameter handling according to W3; see
        // https://www.w3.org/TR/SVG11/implnote.html#ArcImplementationNotes
        if (self::pointsClose($start, $end)) {
            // arc with equal points is treated as nonexistent
            return [];
        }
        $radiusX = abs($radiusX);
        $radiusY = abs($radiusY);
        if ($radiusX < self::$EPSILON || $radiusY < self::$EPSILON) {
            // arc with no radius is treated as straight line
            return [$start, $end];
        }

        $cosr = cos($rotation);
        $sinr = sin($rotation);

        list($center, $radiusX, $radiusY, $angleStart, $angleDelta) =
            self::endpointToCenter($start, $end, $large, $sweep, $radiusX, $radiusY, $cosr, $sinr);

        $dist = abs($end[0] - $start[0]) + abs($end[1] - $start[1]);
        $numSteps = max(2, ceil(abs($angleDelta * $dist * $scale)));
        $stepSize = $angleDelta / $numSteps;

        $points = [];

        for ($i = 0; $i <= $numSteps; ++$i) {
            $angle = $angleStart + $stepSize * $i;
            $first = $radiusX * cos($angle);
            $second = $radiusY * sin($angle);

            $points[] = [
                $cosr * $first - $sinr * $second + $center[0],
                $sinr * $first + $cosr * $second + $center[1],
            ];
        }

        return $points;
    }

    /**
     * Converts an ellipse in endpoint parameterization (standard for SVG paths)
     * to the corresponding center parameterization (easier to work with).
     *
     * In other words, takes two points, sweep flags, and size/orientation
     * values and computes from them the ellipse's optimal center point and the
     * angles the segment covers. For this, the start angle and the angle delta
     * are returned.
     *
     * If the radii are too small, they are scaled. The new radii are returned.
     *
     * The formulas can be found in W3's SVG spec.
     *
     * @see https://www.w3.org/TR/SVG11/implnote.html#ArcImplementationNotes
     *
     * @param float[] $start   The start point (x0, y0).
     * @param float[] $end     The end point (x1, y1).
     * @param bool    $large   The large arc flag.
     * @param bool    $sweep   The sweep direction flag.
     * @param float   $radiusX The x radius.
     * @param float   $radiusY The y radius.
     * @param float   $cosr    Cosine of the ellipse's rotation.
     * @param float   $sinr    Sine of the ellipse's rotation.
     *
     * @return float[] A tuple with (center(cx,cy), radiusX, radiusY, angleStart, angleDelta).
     */
    private static function endpointToCenter(
        array $start,
        array $end,
        bool $large,
        bool $sweep,
        float $radiusX,
        float $radiusY,
        float $cosr,
        float $sinr
    ): array {
        // Step 1: Compute (x1', y1') [F.6.5.1]
        $xsubhalf = ($start[0] - $end[0]) / 2;
        $ysubhalf = ($start[1] - $end[1]) / 2;
        $x1prime  = $cosr * $xsubhalf + $sinr * $ysubhalf;
        $y1prime  = -$sinr * $xsubhalf + $cosr * $ysubhalf;

        // squares that occur multiple times
        $rx2 = $radiusX * $radiusX;
        $ry2 = $radiusY * $radiusY;
        $x1prime2 = $x1prime * $x1prime;
        $y1prime2 = $y1prime * $y1prime;

        // Ensure radiuses are large enough [F.6.6.2]
        $lambdaSqrt = sqrt($x1prime2 / $rx2 + $y1prime2 / $ry2);
        if ($lambdaSqrt > 1) {
            $radiusX *= $lambdaSqrt;
            $radiusY *= $lambdaSqrt;
            $rx2 = $radiusX * $radiusX;
            $ry2 = $radiusY * $radiusY;
        }

        // Step 2: Compute (cx', cy') [F.6.5.2]
        $cxfactor = ($large != $sweep ? 1 : -1) * sqrt(abs(
            ($rx2 * $ry2 - $rx2 * $y1prime2 - $ry2 * $x1prime2) / ($rx2 * $y1prime2 + $ry2 * $x1prime2)
        ));
        $cxprime = $cxfactor *  $radiusX * $y1prime / $radiusY;
        $cyprime = $cxfactor * -$radiusY * $x1prime / $radiusX;

        // Step 3: Compute (cx, cy) from (cx', cy') [F.6.5.3]
        $centerX = $cosr * $cxprime - $sinr * $cyprime + ($start[0] + $end[0]) / 2;
        $centerY = $sinr * $cxprime + $cosr * $cyprime + ($start[1] + $end[1]) / 2;

        // Step 4: Compute the angles [F.6.5.5, F.6.5.6]
        $angleStart = self::vectorAngle(
            ($x1prime - $cxprime) / $radiusX,
            ($y1prime - $cyprime) / $radiusY
        );
        $angleDelta = self::vectorAngle2(
            ( $x1prime - $cxprime) / $radiusX,
            ( $y1prime - $cyprime) / $radiusY,
            (-$x1prime - $cxprime) / $radiusX,
            (-$y1prime - $cyprime) / $radiusY
        );

        // Adapt angles to sweep flags
        if (!$sweep && $angleDelta > 0) {
            $angleDelta -= M_PI * 2;
        } elseif ($sweep && $angleDelta < 0) {
            $angleDelta += M_PI * 2;
        }

        return [[$centerX, $centerY], $radiusX, $radiusY, $angleStart, $angleDelta];
    }

    /**
     * Computes the angle between a vector and the positive x axis.
     * This is a simplified version of vectorAngle2, where the first vector is
     * fixed as [1, 0].
     *
     * @param float $vecx The vector's x coordinate.
     * @param float $vecy The vector's y coordinate.
     *
     * @return float The angle, in radians.
     */
    private static function vectorAngle(float $vecx, float $vecy): float
    {
        $norm = hypot($vecx, $vecy);
        return ($vecy >= 0 ? 1 : -1) * acos($vecx / $norm);
    }

    /**
     * Computes the angle between two given vectors.
     *
     * @param float $vec1x First vector's x coordinate.
     * @param float $vec1y First vector's y coordinate.
     * @param float $vec2x Second vector's x coordinate.
     * @param float $vec2y Second vector's y coordinate.
     *
     * @return float The angle, in radians.
     */
    private static function vectorAngle2(float $vec1x, float $vec1y, float $vec2x, float $vec2y): float
    {
        // see W3C [F.6.5.4]
        $dotprod = $vec1x * $vec2x + $vec1y * $vec2y;
        $norm = hypot($vec1x, $vec1y) * hypot($vec2x, $vec2y);

        $sign = ($vec1x * $vec2y - $vec1y * $vec2x) >= 0 ? 1 : -1;

        return $sign * acos($dotprod / $norm);
    }

    /**
     * Determine whether two points are basically the same, except for minuscule
     * differences.
     *
     * @param float[] $vec1 The start point (x0, y0).
     * @param float[] $vec2 The end point (x1, y1).
     * @return bool Whether the points are close.
     */
    private static function pointsClose(array $vec1, array $vec2): bool
    {
        $distanceX = abs($vec1[0] - $vec2[0]);
        $distanceY = abs($vec1[1] - $vec2[1]);

        return $distanceX < self::$EPSILON && $distanceY < self::$EPSILON;
    }
}
