<?php

namespace JangoBrick\SVG\Rasterization\Path;

class SVGBezierApproximator
{
    public function quadratic($p0, $p1, $p2, $accuracy = 1)
    {
        $t      = 0;
        $prev   = $p0;
        $points = array($p0);

        while ($t < 1) {
            $step  = 0.2;

            do {
                $step /= 2;
                $point = self::calculateQuadratic($p0, $p1, $p2, $t + $step);
                $dist  = self::getDistanceSquared($prev, $point);
            } while ($dist > $accuracy);

            $points[] = $prev = $point;
            $t += $step;
        }

        return $points;
    }

    public function cubic($p0, $p1, $p2, $p3, $accuracy = 1)
    {
        $t      = 0;
        $prev   = $p0;
        $points = array($p0);

        while ($t < 1) {
            $step  = 0.2;

            do {
                $step /= 2;
                $point = self::calculateCubic($p0, $p1, $p2, $p3, $t + $step);
                $dist  = self::getDistanceSquared($prev, $point);
            } while ($dist > $accuracy);

            $points[] = $prev = $point;
            $t += $step;
        }

        return $points;
    }



    private static function calculateQuadratic($p0, $p1, $p2, $t)
    {
        $ti = 1 - $t;

        return array(
            $ti * $ti * $p0[0] + 2 * $ti * $t * $p1[0] + $t * $t * $p2[0],
            $ti * $ti * $p0[1] + 2 * $ti * $t * $p1[1] + $t * $t * $p2[1],
        );
    }

    private static function calculateCubic($p0, $p1, $p2, $p3, $t)
    {
        $ti = 1 - $t;

        // first step: lines between the given points
        $a0x = $ti * $p0[0] + $t * $p1[0];
        $a0y = $ti * $p0[1] + $t * $p1[1];
        $a1x = $ti * $p1[0] + $t * $p2[0];
        $a1y = $ti * $p1[1] + $t * $p2[1];
        $a2x = $ti * $p2[0] + $t * $p3[0];
        $a2y = $ti * $p2[1] + $t * $p3[1];

        // second step: lines between points from step 2
        $b0x = $ti * $a0x + $t * $a1x;
        $b0y = $ti * $a0y + $t * $a1y;
        $b1x = $ti * $a1x + $t * $a2x;
        $b1y = $ti * $a1y + $t * $a2y;

        // last step: line between points from step 3, result
        return array(
            $ti * $b0x + $t * $b1x,
            $ti * $b0y + $t * $b1y,
        );
    }



    private static function getDistanceSquared($p1, $p2)
    {
        $dx = $p2[0] - $p1[0];
        $dy = $p2[1] - $p1[1];

        return $dx * $dx + $dy * $dy;
    }
}
