<?php

namespace JangoBrick\SVG\Nodes\Shapes;

use JangoBrick\SVG\Nodes\SVGNode;
use JangoBrick\SVG\Rasterization\SVGRasterizer;

/**
 * Represents the SVG tag 'path'.
 */
class SVGPath extends SVGNode
{
    const TAG_NAME = 'path';

    /**
     * @param string|null $d The path description.
     */
    public function __construct($d = null)
    {
        parent::__construct();

        $this->setAttributeOptional($d);
    }



    public function rasterize(SVGRasterizer $rasterizer)
    {
        $d = $this->getAttribute('d');

        $commands = $rasterizer->getPathParser()->parse($d);
        $subpaths = $rasterizer->getPathApproximator()->approximate($commands);

        foreach ($subpaths as $subpath) {
            $rasterizer->render('polygon', array(
                'open'      => true,
                'points'    => $subpath,
            ), $this);
        }
    }
}
