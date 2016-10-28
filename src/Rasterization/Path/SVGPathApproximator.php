<?php

namespace JangoBrick\SVG\Rasterization\Path;

class SVGPathApproximator
{
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

    private static $bezier;

    public function __construct()
    {
        if (isset(self::$bezier)) {
            return;
        }
        self::$bezier = new SVGBezierApproximator();
    }



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
     * @SuppressWarnings("unused")
     */
    private function closePath($id, $args, SVGPolygonBuilder $builder)
    {
        $first = $builder->getFirstPoint();
        $builder->addPoint($first[0], $first[1]);
    }
}
