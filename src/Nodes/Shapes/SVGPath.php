<?php

namespace JangoBrick\SVG\Nodes\Shapes;

use JangoBrick\SVG\Nodes\SVGNode;
use JangoBrick\SVG\Rasterization\SVGRasterizer;

class SVGPath extends SVGNode
{
    private $d;

    public function __construct($d)
    {
        parent::__construct('path');

        $this->d = $d;
    }

    public static function constructFromAttributes($attrs)
    {
        $d = isset($attrs['d']) ? $attrs['d'] : '';

        return new self($d);
    }

    public function getSerializableAttributes()
    {
        $attrs = parent::getSerializableAttributes();

        $attrs['d'] = $this->d;

        return $attrs;
    }

    public function rasterize(SVGRasterizer $rasterizer)
    {
        $commands = $rasterizer->getPathParser()->parse($this->d);
        $subpaths = $rasterizer->getPathApproximator()->approximate($commands);

        foreach ($subpaths as $subpath) {
            $rasterizer->render('polygon', array(
                'open'      => true,
                'points'    => $subpath,
            ), $this);
        }
    }
}
