<?php

class SVGGroup extends SVGNodeContainer {

    public function __construct() {
        parent::__construct();
    }





    public function toXMLString() {

        $s  = '<g';

        if ($this->x != 0)
            $s .= ' x="'.$this->x.'"';
        if ($this->y != 0)
            $s .= ' y="'.$this->y.'"';

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





    public function draw($image, $imageWidth, $imageHeight, $scaleX, $scaleY, $offsetX = 0, $offsetY = 0) {

    }

}