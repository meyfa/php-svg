<?php

namespace JangoBrick\SVG\Rasterization\Path;

/**
 * This class can trace a path by converting its commands into a series of
 * points. Curves are approximated and treated like polyline segments.
 */
class SVGPathApproximator
{
    /**
     * @var string[] $commands A map of command ids to approximation functions.
     */
    private static $commands = array(
        'M' => 'moveTo',            'm' => 'moveTo',
        'L' => 'lineTo',            'l' => 'lineTo',
        'H' => 'lineToHorizontal',  'h' => 'lineToHorizontal',
        'V' => 'lineToVertical',    'v' => 'lineToVertical',
        'C' => 'curveToCubic',      'c' => 'curveToCubic',
        'Q' => 'curveToQuadratic',  'q' => 'curveToQuadratic',
        //TODO implement ArcTo
        'Z' => 'closePath',         'z' => 'closePath',
    );

    /**
     * @var SVGBezierApproximator $bezier The singleton bezier approximator.
     */
    private static $bezier;

    public function __construct()
    {
        if (isset(self::$bezier)) {
            return;
        }
        self::$bezier = new SVGBezierApproximator();
    }



    /**
     * Traces/approximates the path described by the given array of commands.
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
     * The return value is an array of subpaths -- parts of the path that aren't
     * interconnected. Each subpath, then, is an array containing points as
     * 2-tuples of type float.
     * For example, the input above would yield:
     * `[[[10, 20], [50, 40], [10, 20]]]`
     *
     * @param array[] $cmds The commands (assoc. arrays; see above).
     *
     * @return array[] An array of subpaths, which are arrays of points.
     */
    public function approximate(array $cmds)
    {
        $subpaths = array();

        $posX = 0;
        $posY = 0;

        $sp = array();

        foreach ($cmds as $cmd) {
            if (($cmd['id'] === 'M' || $cmd['id'] === 'm') && !empty($sp)) {
                $subpaths[] = $this->approximateSubpath($sp, $posX, $posY);
                $sp = array();
            }
            $sp[] = $cmd;
        }

        if (!empty($sp)) {
            $subpaths[] = $this->approximateSubpath($sp, $posX, $posY);
        }

        return $subpaths;
    }

    /**
     * Traces/approximates a path known to be continuous which is described by
     * the given array of commands.
     *
     * For input data format, see `approximate()`.
     *
     * The return value is a single array of approximated points. In addition,
     * the final x and y coordinates are stored in their respective reference
     * parameters.
     *
     * @param array[] $cmds The commands (assoc. arrays; see above).
     * @param float   $posX The current x position.
     * @param float   $posY The current y position.
     *
     * @return array[] An array of points approximately describing the subpath.
     */
    private function approximateSubpath(array $cmds, &$posX, &$posY)
    {
        $builder = new SVGPolygonBuilder($posX, $posY);

        foreach ($cmds as $cmd) {
            $id = $cmd['id'];
            if (!isset(self::$commands[$id])) {
                return false;
            }
            $funcName = self::$commands[$id];
            $this->$funcName($id, $cmd['args'], $builder);
        }

        $pos  = $builder->getPosition();
        $posX = $pos[0];
        $posY = $pos[1];

        return $builder->build();
    }



    /**
     * Approximation function for MoveTo (M and m).
     *
     * @param string            $id      The actual id used (for abs. vs. rel.).
     * @param float[]           $args    The arguments provided to the command.
     * @param SVGPolygonBuilder $builder The subpath builder to append to.
     *
     * @return void
     *
     * @SuppressWarnings("unused")
     */
    private function moveTo($id, $args, SVGPolygonBuilder $builder)
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
     * @param string            $id      The actual id used (for abs. vs. rel.).
     * @param float[]           $args    The arguments provided to the command.
     * @param SVGPolygonBuilder $builder The subpath builder to append to.
     *
     * @return void
     *
     * @SuppressWarnings("unused")
     */
    private function lineTo($id, $args, SVGPolygonBuilder $builder)
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
     * @param string            $id      The actual id used (for abs. vs. rel.).
     * @param float[]           $args    The arguments provided to the command.
     * @param SVGPolygonBuilder $builder The subpath builder to append to.
     *
     * @return void
     *
     * @SuppressWarnings("unused")
     */
    private function lineToHorizontal($id, $args, SVGPolygonBuilder $builder)
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
     * @param string            $id      The actual id used (for abs. vs. rel.).
     * @param float[]           $args    The arguments provided to the command.
     * @param SVGPolygonBuilder $builder The subpath builder to append to.
     *
     * @return void
     *
     * @SuppressWarnings("unused")
     */
    private function lineToVertical($id, $args, SVGPolygonBuilder $builder)
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
     * @param string            $id      The actual id used (for abs. vs. rel.).
     * @param float[]           $args    The arguments provided to the command.
     * @param SVGPolygonBuilder $builder The subpath builder to append to.
     *
     * @return void
     *
     * @SuppressWarnings("unused")
     */
    private function curveToCubic($id, $args, SVGPolygonBuilder $builder)
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
    }

    /**
     * Approximation function for CurveToQuadratic (Q and q).
     *
     * @param string            $id      The actual id used (for abs. vs. rel.).
     * @param float[]           $args    The arguments provided to the command.
     * @param SVGPolygonBuilder $builder The subpath builder to append to.
     *
     * @return void
     *
     * @SuppressWarnings("unused")
     */
    private function curveToQuadratic($id, $args, SVGPolygonBuilder $builder)
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
    }

    /**
     * Approximation function for ClosePath (Z and z).
     *
     * @param string            $id      The actual id used (for abs. vs. rel.).
     * @param float[]           $args    The arguments provided to the command.
     * @param SVGPolygonBuilder $builder The subpath builder to append to.
     *
     * @return void
     *
     * @SuppressWarnings("unused")
     */
    private function closePath($id, $args, SVGPolygonBuilder $builder)
    {
        $first = $builder->getFirstPoint();
        $builder->addPoint($first[0], $first[1]);
    }
}
