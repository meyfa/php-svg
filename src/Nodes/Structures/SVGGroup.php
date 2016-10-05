<?php

namespace JangoBrick\SVG\Nodes\Structures;

use JangoBrick\SVG\Nodes\SVGNodeContainer;
use JangoBrick\SVG\SVGRenderingHelper;

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

    public function draw(SVGRenderingHelper $rh, $scaleX, $scaleY)
    {

        // cannot inherit opacity, so getStyle instead of getComputedStyle
        $opacity = $this->getStyle('opacity');
        if (isset($opacity) && is_numeric($opacity)) {
            $opacity = floatval($opacity);
        } else {
            $opacity = 1;
        }

        if ($opacity < 1) {
            $buffer = $rh->createBuffer();
            for ($i = 0, $n = $this->countChildren(); $i < $n; ++$i) {
                $child = $this->getChild($i);
                $child->draw($buffer, $scaleX, $scaleY);
            }
            $rh->drawBuffer($buffer, $opacity);
        } else {
            for ($i = 0, $n = $this->countChildren(); $i < $n; ++$i) {
                $child = $this->getChild($i);
                $child->draw($rh, $scaleX, $scaleY);
            }
        }
    }
}
