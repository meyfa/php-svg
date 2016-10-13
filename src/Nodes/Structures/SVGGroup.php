<?php

namespace JangoBrick\SVG\Nodes\Structures;

use JangoBrick\SVG\Nodes\SVGNodeContainer;

class SVGGroup extends SVGNodeContainer
{
    public function __construct()
    {
        parent::__construct('g');
    }
}
