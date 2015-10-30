<?php

class SVGGroup extends SVGNodeContainer {

    public function __construct() {
        parent::__construct();
    }





    public function toXMLString() {

        $s  = '<g';

        if (!empty($this->styles)) {
            $s .= ' style="';
            foreach ($this->styles as $style => $value) {
                $s .= $style . ': ' . $value . '; ';
            }
            $s .= '"';
        }

        $s .= '>';

        for ($i=0, $n=$this->countChildren(); $i<$n; $i++) {
            $child = $this->getChild($i);
            $s .= $child->toXMLString();
        }

        $s .= '</g>';

        return $s;

    }





    public function draw(SVGRenderingHelper $rh, $scaleX, $scaleY, $offsetX = 0, $offsetY = 0) {

        // cannot inherit opacity, so getStyle instead of getComputedStyle
        $opacity = $this->getStyle('opacity');
        if (isset($opacity) && is_numeric($opacity)) {
            $opacity = floatval($opacity);
        } else {
            $opacity = 1;
        }

        if ($opacity < 1) {
            $buffer = $rh->createBuffer();
            for ($i=0, $n=$this->countChildren(); $i<$n; $i++) {
                $child = $this->getChild($i);
                $child->draw($buffer, $scaleX, $scaleY, $offsetX, $offsetY);
            }
            $rh->drawBuffer($buffer, $opacity);
        } else {
            for ($i=0, $n=$this->countChildren(); $i<$n; $i++) {
                $child = $this->getChild($i);
                $child->draw($rh, $scaleX, $scaleY, $offsetX, $offsetY);
            }
        }

    }

}
