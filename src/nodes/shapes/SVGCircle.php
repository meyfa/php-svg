<?php

class SVGCircle extends SVGNode {

    private $cx, $cy, $r;



    public function __construct($cx, $cy, $r) {
        $this->cx = $cx;
        $this->cy = $cy;
        $this->r = $r;
    }





    public function toXMLString() {

        $s  = '<circle';

        $s .= ' cx="'.$this->cx.'"';
        $s .= ' cy="'.$this->cy.'"';
        $s .= ' r="'.$this->r.'"';

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

}