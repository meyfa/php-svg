<?php

namespace JangoBrick\SVG\Nodes\Structures;

use JangoBrick\SVG\Nodes\SVGNodeContainer;

class SVGGroup extends SVGNodeContainer
{
    public function __construct()
    {
        parent::__construct();
    }

    public function toXMLString()
    {
        $s  = '<g';

        $this->addStylesToXMLString($s);
        $this->addAttributesToXMLString($s);

        $s .= '>';

        for ($i = 0, $n = $this->countChildren(); $i < $n; ++$i) {
            $child = $this->getChild($i);
            $s .= $child->toXMLString();
        }

        $s .= '</g>';

        return $s;
    }
}
