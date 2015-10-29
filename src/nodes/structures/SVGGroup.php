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

        for ($i=0, $n=$this->countChildren(); $i<$n; $i++) {
            $child = $this->getChild($i);
            $child->draw($rh, $scaleX, $scaleY, $offsetX, $offsetY);
        }

    }

}
