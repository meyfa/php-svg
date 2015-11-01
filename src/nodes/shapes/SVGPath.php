<?php

class SVGPath extends SVGNode {

    private $d;



    public function __construct($d) {
        $this->d = $d;
    }





    public function toXMLString() {

        $s  = '<path';

        $s .= ' d="'.$this->d.'"';

        if (!empty($this->styles)) {
            $s .= ' style="';
            foreach ($this->styles as $style => $value) {
                $s .= $style . ': ' . $value . '; ';
            }
            $s .= '"';
        }

        $s .= ' />';

        return $s;

    }





    public function draw(SVGRenderingHelper $rh, $scaleX, $scaleY, $offsetX = 0, $offsetY = 0) {

    }

}
