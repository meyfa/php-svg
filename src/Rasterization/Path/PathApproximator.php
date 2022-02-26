<?php

namespace SVG\Rasterization\Path;

use SVG\Rasterization\Transform\Transform;

/**
 * This class can trace a path by converting its commands into a series of points. Curves are approximated and output
 * as polyline segments.
 *
 * The transform to use for mapping coordinates into the final image must be supplied during this step, so that
 * the approximation accuracy for each segment can be properly chosen according to the output resolution instead of
 * the input coordinates (which may have a completely arbitrary scale).
 *
 * There should be one instance created per path, as this class must keep some state during processing.
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
     * @var Transform $transform The transform to use.
     */
    private $transform;

    /**
     * @var float[][][] The approximation result up until now.
     */
    private $subpaths = array();

    // the start of the current subpath, in path coordinates
    private $firstX;
    private $firstY;

    // the most recently added point of the current subpath, in path coordinates
    private $posX;
    private $posY;

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

    /**
     * Construct a new, empty approximator.
     *
     * @param Transform $transform The transform from path coordinates into image coordinates.
     */
    public function __construct(Transform $transform)
    {
        $this->transform = $transform;

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
        // These variables are used to track the current position in *path coordinate space*.
        // We cannot simply use the PolygonBuilder's last position for this, because that has already been transformed.
        $this->posX = 0;
        $this->posY = 0;

        $sp = array();

        foreach ($commands as $cmd) {
            if (($cmd['id'] === 'M' || $cmd['id'] === 'm') && !empty($sp)) {
                $this->subpaths[] = $this->approximateSubpath($sp);
                $sp = array();
            }
            $sp[] = $cmd;
        }

        if (!empty($sp)) {
            $this->subpaths[] = $this->approximateSubpath($sp);
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
     *
     * @return array[] An array of points approximately describing the subpath.
     * @see PathApproximator::approximate() For an input format description.
     */
    private function approximateSubpath(array $commands)
    {
        $this->firstX = $this->posX;
        $this->firstY = $this->posY;

        $builderX = $this->posX;
        $builderY = $this->posY;
        $this->transform->map($builderX, $builderY);
        $builder = new PolygonBuilder($builderX, $builderY);

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
        list($x, $y) = $args;
        if ($id === 'm') {
            $x += $this->posX;
            $y += $this->posY;
        }
        $this->firstX = $this->posX = $x;
        $this->firstY = $this->posY = $y;
        $this->transform->map($x, $y);
        $builder->addPoint($x, $y);
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
        list($x, $y) = $args;
        if ($id === 'l') {
            $x += $this->posX;
            $y += $this->posY;
        }
        $this->posX = $x;
        $this->posY = $y;
        $this->transform->map($x, $y);
        $builder->addPoint($x, $y);
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
        $x = $args[0];
        $y = $this->posY;
        if ($id === 'h') {
            $x += $this->posX;
        }
        $this->posX = $x;
        $this->transform->map($x, $y);
        $builder->addPoint($x, $y);
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
        $x = $this->posX;
        $y = $args[0];
        if ($id === 'v') {
            $y += $this->posY;
        }
        $this->posY = $y;
        $this->transform->map($x, $y);
        $builder->addPoint($x, $y);
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
        // NOTE: Bézier curves are invariant under affine transforms.
        //       This means transforming the control points vs. transforming the final approximated pixels does not
        //       affect the nature of the curve. This is great! By transforming first, we can choose the approximation
        //       accuracy properly for the output image size.

        // the transformed $p0 is simply $builder->getPosition()
        $p1 = array($args[0], $args[1]);
        $p2 = array($args[2], $args[3]);
        $p3 = array($args[4], $args[5]);

        if ($id === 'c') {
            $p1[0] += $this->posX;
            $p1[1] += $this->posY;

            $p2[0] += $this->posX;
            $p2[1] += $this->posY;

            $p3[0] += $this->posX;
            $p3[1] += $this->posY;
        }

        $this->cubicOld = $p2;
        list($this->posX, $this->posY) = $p3;

        $this->transform->map($p1[0], $p1[1]);
        $this->transform->map($p2[0], $p2[1]);
        $this->transform->map($p3[0], $p3[1]);

        $approx = self::$bezier->cubic($builder->getPosition(), $p1, $p2, $p3);
        $builder->addPoints($approx);
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
        $p1 = array($this->posX, $this->posY); // first control point defaults to current point
        $p2 = array($args[0], $args[1]);
        $p3 = array($args[2], $args[3]);

        if ($id === 's') {
            $p2[0] += $this->posX;
            $p2[1] += $this->posY;

            $p3[0] += $this->posX;
            $p3[1] += $this->posY;
        }

        // calculate first control point
        $prev = strtolower($this->previousCommand);
        if ($prev === 'c' || $prev === 's') {
            $p1 = self::reflectPoint($this->cubicOld, $p1);
        }

        $this->cubicOld = $p2;
        list($this->posX, $this->posY) = $p3;

        $this->transform->map($p1[0], $p1[1]);
        $this->transform->map($p2[0], $p2[1]);
        $this->transform->map($p3[0], $p3[1]);

        $approx = self::$bezier->cubic($builder->getPosition(), $p1, $p2, $p3);
        $builder->addPoints($approx);
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
        $p1 = array($args[0], $args[1]);
        $p2 = array($args[2], $args[3]);

        if ($id === 'q') {
            $p1[0] += $this->posX;
            $p1[1] += $this->posY;

            $p2[0] += $this->posX;
            $p2[1] += $this->posY;
        }

        $this->quadraticOld = $p1;
        list($this->posX, $this->posY) = $p2;

        $this->transform->map($p1[0], $p1[1]);
        $this->transform->map($p2[0], $p2[1]);

        $approx = self::$bezier->quadratic($builder->getPosition(), $p1, $p2);
        $builder->addPoints($approx);
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
        $p1 = array($this->posX, $this->posY); // control point defaults to current point
        $p2 = array($args[0], $args[1]);

        if ($id === 't') {
            $p2[0] += $this->posX;
            $p2[1] += $this->posY;
        }

        // calculate control point
        $prev = strtolower($this->previousCommand);
        if ($prev === 'q' || $prev === 't') {
            $p1 = self::reflectPoint($this->quadraticOld, $p1);
        }

        $this->quadraticOld = $p1;
        list($this->posX, $this->posY) = $p2;

        $this->transform->map($p1[0], $p1[1]);
        $this->transform->map($p2[0], $p2[1]);

        $approx = self::$bezier->quadratic($builder->getPosition(), $p1, $p2);
        $builder->addPoints($approx);
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
        // NOTE: Unfortunately, it seems that arc segments are not invariant under affine transforms, as opposed to
        //       Bézier curves. Currently, our best strategy is to approximate the curve with path coordinates
        //       and transform the resulting points. This is very suboptimal for both performance and visual fidelity.
        // TODO transform command before approximating

        // start point, end point
        $p0 = array($this->posX, $this->posY);
        $p1 = array($args[5], $args[6]);
        // radiuses, rotation
        $rx = $args[0];
        $ry = $args[1];
        $xa = deg2rad($args[2]);
        // large arc flag, sweep flag
        $fa = (bool) $args[3];
        $fs = (bool) $args[4];

        if ($id === 'a') {
            $p1[0] += $this->posX;
            $p1[1] += $this->posY;
        }

        $approx = self::$arc->approximate($p0, $p1, $fa, $fs, $rx, $ry, $xa);

        foreach ($approx as $point) {
            $this->transform->map($point[0], $point[1]);
        }
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

        $this->posX = $this->firstX;
        $this->posY = $this->firstY;
    }
}
