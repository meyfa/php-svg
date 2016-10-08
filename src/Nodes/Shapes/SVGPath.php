<?php

namespace JangoBrick\SVG\Nodes\Shapes;

use JangoBrick\SVG\Nodes\SVGNode;
use JangoBrick\SVG\Rasterization\SVGRasterizer;

class SVGPath extends SVGNode
{
    private $d;

    public function __construct($d)
    {
        parent::__construct();

        $this->d = $d;
    }

    public function toXMLString()
    {
        $s  = '<path';

        $s .= ' d="'.$this->d.'"';

        $this->addStylesToXMLString($s);
        $this->addAttributesToXMLString($s);

        $s .= ' />';

        return $s;
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
