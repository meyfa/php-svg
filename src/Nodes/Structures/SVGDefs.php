<?php

namespace SVG\Nodes\Structures;

use SVG\Nodes\SVGNodeContainer;

/**
 * Represents the SVG tag 'defs'.
 */
class SVGDefs extends SVGNodeContainer
{
    const TAG_NAME = 'defs';

    public function __construct()
    {
        parent::__construct();
    }
}
