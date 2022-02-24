<?php

namespace SVG\Rasterization\Path;

/**
 * This class can trace a path by converting its commands into a series of
 * points. Curves are approximated and treated like polyline segments.
 *
 * There should be one instance created per path, as this class must keep some
 * state during processing.
 */
class PathApproximator
{
    /**
     * @var string[] $commands A map of command ids to approximation functions.
     */
    private static $commands = array(
        'M' => 'moveTo',                    'm' => 'moveTo',
        'L' => 'lineTo',                    'l' => 'lineTo',
        'H' => 'lineToHorizontal',          'h' => 'lineToHorizontal',
        'V' => 'lineToVertical',            'v' => 'lineToVertical',
        'C' => 'curveToCubic',              'c' => 'curveToCubic',
        'S' => 'curveToCubicSmooth',        's' => 'curveToCubicSmooth',
        'Q' => 'curveToQuadratic',          'q' => 'curveToQuadratic',
        'T' => 'curveToQuadraticSmooth',    't' => 'curveToQuadraticSmooth',
        'A' => 'arcTo',                     'a' => 'arcTo',
        'Z' => 'closePath',                 'z' => 'closePath',
    );

    /**
     * @var BezierApproximator $bezier The singleton bezier approximator.
     */
    private static $bezier;
    /**
     * @var ArcApproximator $arc The singleton arc approximator.
     */
    private static $arc;

    /**
     * @var float[][][] The approximation result up until now.
     */
    private $subpaths = array();

    /**
     * @var string $previousCommand The id of the last computed command.
     */
    private $previousCommand;
    /**
     * @var float[] $cubicOld Second control point of last C or S command.
     */
    private $cubicOld;
    /**
     * @var float[] $quadraticOld Control point of last Q or T command.
     */
    private $quadraticOld;

    public function __construct()
    {
        if (isset(self::$bezier)) {
            return;
        }
        self::$bezier = new BezierApproximator();
        self::$arc    = new ArcApproximator();
    }

    /**
     * Traces/approximates the path described by the given array of commands.
     * The behavior when this is called multiple times is unspecified.
     *
     * After this function has completed, the resulting subpaths can be obtained via <code>getSubpaths()</code>.
     *
     * Example input:
     * ```php
     * [
     *     ['id' => 'M', 'args' => [10, 20]],
     *     ['id' => 'l', 'args' => [40, 20]],
     *     ['id' => 'Z', 'args' => []],
     * ]
     * ```
     *
     * @param array[] $commands The commands (assoc. arrays; see above).
     *
     * @return void
     */
    public function approximate(array $commands)
    {
        $posX = 0;
        $posY = 0;

        $sp = array();

        foreach ($commands as $cmd) {
            if (($cmd['id'] === 'M' || $cmd['id'] === 'm') && !empty($sp)) {
                $this->subpaths[] = $this->approximateSubpath($sp, $posX, $posY);
                $sp = array();
            }
            $sp[] = $cmd;
        }

        if (!empty($sp)) {
            $this->subpaths[] = $this->approximateSubpath($sp, $posX, $posY);
        }
    }

    /**
     * Obtain the resulting subpath array after approximation.
     *
     * This array contains an entry for each subpath. Such an entry is itself an array of points.
     * Each point is an array of two floats (the x and y coordinates).
     *
     * @return float[][][] The approximated subpaths.
     */
    public function getSubpaths()
    {
        return $this->subpaths;
    }

    /**
     * Traces/approximates a path known to be continuous which is described by
     * the given array of commands.
     *
     * The return value is a single array of approximated points. In addition,
     * the final x and y coordinates are stored in their respective reference
     * parameters.
     *
     * @param array[] $commands The commands (assoc. arrays; see above).
     * @param float   $posX     The current x position.
     * @param float   $posY     The current y position.
     *
     * @return array[] An array of points approximately describing the subpath.
     * @see PathApproximator::approximate() For an input format description.
     */
    private function approximateSubpath(array $commands, &$posX, &$posY)
    {
        $builder = new PolygonBuilder($posX, $posY);

        foreach ($commands as $cmd) {
            $id = $cmd['id'];
            if (!isset(self::$commands[$id])) {
                // https://svgwg.org/svg2-draft/paths.html#PathDataErrorHandling
                // "The general rule for error handling in path data is that the SVG user agent shall render a 'path'
                // element up to (but not including) the path command containing the first error in the path data
                // specification."
                break;
            }
            $funcName = self::$commands[$id];
            $this->$funcName($id, $cmd['args'], $builder);
            $this->previousCommand = $id;
        }

        list($posX, $posY) = $builder->getPosition();

        return $builder->build();
    }

    /**
     * Calculates the reflection of $p relative to $r. Returns a point.
     *
     * @param float[] $p The point to be reflected (x, y).
     * @param float[] $r The point that $p is reflected relative to (x, y).
     *
     * @return float[] The reflected point (x, y).
     */
    private static function reflectPoint(array $p, array $r)
    {
        return array(
            2 * $r[0] - $p[0],
            2 * $r[1] - $p[1],
        );
    }

    /**
     * Approximation function for MoveTo (M and m).
     *
     * @param string         $id      The actual id used (for abs. vs. rel.).
     * @param float[]        $args    The arguments provided to the command.
     * @param PolygonBuilder $builder The subpath builder to append to.
     *
     * @return void
     *
     * @SuppressWarnings("unused")
     * @noinspection PhpUnusedPrivateMethodInspection
     */
    private function moveTo($id, array $args, PolygonBuilder $builder)
    {
        if ($id === 'm') {
            $builder->addPointRelative($args[0], $args[1]);
            return;
        }
        $builder->addPoint($args[0], $args[1]);
    }

    /**
     * Approximation function for LineTo (L and l).
     *
     * @param string         $id      The actual id used (for abs. vs. rel.).
     * @param float[]        $args    The arguments provided to the command.
     * @param PolygonBuilder $builder The subpath builder to append to.
     *
     * @return void
     *
     * @SuppressWarnings("unused")
     * @noinspection PhpUnusedPrivateMethodInspection
     */
    private function lineTo($id, array $args, PolygonBuilder $builder)
    {
        if ($id === 'l') {
            $builder->addPointRelative($args[0], $args[1]);
            return;
        }
        $builder->addPoint($args[0], $args[1]);
    }

    /**
     * Approximation function for LineToHorizontal (H and h).
     *
     * @param string         $id      The actual id used (for abs. vs. rel.).
     * @param float[]        $args    The arguments provided to the command.
     * @param PolygonBuilder $builder The subpath builder to append to.
     *
     * @return void
     *
     * @SuppressWarnings("unused")
     * @noinspection PhpUnusedPrivateMethodInspection
     */
    private function lineToHorizontal($id, array $args, PolygonBuilder $builder)
    {
        if ($id === 'h') {
            $builder->addPointRelative($args[0], null);
            return;
        }
        $builder->addPoint($args[0], null);
    }

    /**
     * Approximation function for LineToVertical (V and v).
     *
     * @param string         $id      The actual id used (for abs. vs. rel.).
     * @param float[]        $args    The arguments provided to the command.
     * @param PolygonBuilder $builder The subpath builder to append to.
     *
     * @return void
     *
     * @SuppressWarnings("unused")
     * @noinspection PhpUnusedPrivateMethodInspection
     */
    private function lineToVertical($id, array $args, PolygonBuilder $builder)
    {
        if ($id === 'v') {
            $builder->addPointRelative(null, $args[0]);
            return;
        }
        $builder->addPoint(null, $args[0]);
    }

    /**
     * Approximation function for CurveToCubic (C and c).
     *
     * @param string         $id      The actual id used (for abs. vs. rel.).
     * @param float[]        $args    The arguments provided to the command.
     * @param PolygonBuilder $builder The subpath builder to append to.
     *
     * @return void
     *
     * @SuppressWarnings("unused")
     * @noinspection PhpUnusedPrivateMethodInspection
     */
    private function curveToCubic($id, array $args, PolygonBuilder $builder)
    {
        $p0 = $builder->getPosition();
        $p1 = array($args[0], $args[1]);
        $p2 = array($args[2], $args[3]);
        $p3 = array($args[4], $args[5]);

        if ($id === 'c') {
            $p1[0] += $p0[0];
            $p1[1] += $p0[1];

            $p2[0] += $p0[0];
            $p2[1] += $p0[1];

            $p3[0] += $p0[0];
            $p3[1] += $p0[1];
        }

        $approx = self::$bezier->cubic($p0, $p1, $p2, $p3);
        $builder->addPoints($approx);

        $this->cubicOld = $p2;
    }

    /**
     * Approximation function for CurveToCubicSmooth (S and s).
     *
     * @param string         $id      The actual id used (for abs. vs. rel.).
     * @param float[]        $args    The arguments provided to the command.
     * @param PolygonBuilder $builder The subpath builder to append to.
     *
     * @return void
     *
     * @SuppressWarnings("unused")
     * @noinspection PhpUnusedPrivateMethodInspection
     */
    private function curveToCubicSmooth($id, array $args, PolygonBuilder $builder)
    {
        $p0 = $builder->getPosition();
        $p1 = $p0; // first control point defaults to current point
        $p2 = array($args[0], $args[1]);
        $p3 = array($args[2], $args[3]);

        if ($id === 's') {
            $p2[0] += $p0[0];
            $p2[1] += $p0[1];

            $p3[0] += $p0[0];
            $p3[1] += $p0[1];
        }

        // calculate first control point
        $prev = strtolower($this->previousCommand);
        if ($prev === 'c' || $prev === 's') {
            $p1 = self::reflectPoint($this->cubicOld, $p0);
        }

        $approx = self::$bezier->cubic($p0, $p1, $p2, $p3);
        $builder->addPoints($approx);

        $this->cubicOld = $p2;
    }

    /**
     * Approximation function for CurveToQuadratic (Q and q).
     *
     * @param string         $id      The actual id used (for abs. vs. rel.).
     * @param float[]        $args    The arguments provided to the command.
     * @param PolygonBuilder $builder The subpath builder to append to.
     *
     * @return void
     *
     * @SuppressWarnings("unused")
     * @noinspection PhpUnusedPrivateMethodInspection
     */
    private function curveToQuadratic($id, array $args, PolygonBuilder $builder)
    {
        $p0 = $builder->getPosition();
        $p1 = array($args[0], $args[1]);
        $p2 = array($args[2], $args[3]);

        if ($id === 'q') {
            $p1[0] += $p0[0];
            $p1[1] += $p0[1];

            $p2[0] += $p0[0];
            $p2[1] += $p0[1];
        }

        $approx = self::$bezier->quadratic($p0, $p1, $p2);
        $builder->addPoints($approx);

        $this->quadraticOld = $p1;
    }

    /**
     * Approximation function for CurveToQuadraticSmooth (T and t).
     *
     * @param string         $id      The actual id used (for abs. vs. rel.).
     * @param float[]        $args    The arguments provided to the command.
     * @param PolygonBuilder $builder The subpath builder to append to.
     *
     * @return void
     *
     * @SuppressWarnings("unused")
     * @noinspection PhpUnusedPrivateMethodInspection
     */
    private function curveToQuadraticSmooth($id, array $args, PolygonBuilder $builder)
    {
        $p0 = $builder->getPosition();
        $p1 = $p0; // control point defaults to current point
        $p2 = array($args[0], $args[1]);

        if ($id === 't') {
            $p2[0] += $p0[0];
            $p2[1] += $p0[1];
        }

        // calculate control point
        $prev = strtolower($this->previousCommand);
        if ($prev === 'q' || $prev === 't') {
            $p1 = self::reflectPoint($this->quadraticOld, $p0);
        }

        $approx = self::$bezier->quadratic($p0, $p1, $p2);
        $builder->addPoints($approx);

        $this->quadraticOld = $p1;
    }

    /**
     * Approximation function for ArcTo (A and a).
     *
     * @param string         $id      The actual id used (for abs. vs. rel.).
     * @param float[]        $args    The arguments provided to the command.
     * @param PolygonBuilder $builder The subpath builder to append to.
     *
     * @return void
     *
     * @SuppressWarnings("unused")
     * @noinspection PhpUnusedPrivateMethodInspection
     */
    private function arcTo($id, array $args, PolygonBuilder $builder)
    {
        // start point, end point
        $p0 = $builder->getPosition();
        $p1 = array($args[5], $args[6]);
        // radiuses, rotation
        $rx = $args[0];
        $ry = $args[1];
        $xa = deg2rad($args[2]);
        // large arc flag, sweep flag
        $fa = (bool) $args[3];
        $fs = (bool) $args[4];

        if ($id === 'a') {
            $p1[0] += $p0[0];
            $p1[1] += $p0[1];
        }

        $approx = self::$arc->approximate($p0, $p1, $fa, $fs, $rx, $ry, $xa);
        $builder->addPoints($approx);
    }

    /**
     * Approximation function for ClosePath (Z and z).
     *
     * @param string         $id      The actual id used (for abs. vs. rel.).
     * @param float[]        $args    The arguments provided to the command.
     * @param PolygonBuilder $builder The subpath builder to append to.
     *
     * @return void
     *
     * @SuppressWarnings("unused")
     * @noinspection PhpUnusedPrivateMethodInspection
     */
    private function closePath($id, array $args, PolygonBuilder $builder)
    {
        $first = $builder->getFirstPoint();
        $builder->addPoint($first[0], $first[1]);
    }
}
