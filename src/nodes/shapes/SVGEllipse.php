<?php

class SVGEllipse extends SVGNode {

    private $cx, $cy, $rx, $ry;



    public function __construct($cx, $cy, $rx, $ry) {
        $this->cx = $cx;
        $this->cy = $cy;
        $this->rx = $rx;
        $this->ry = $ry;
    }





    public function toXMLString() {

        $s  = '<ellipse';

        $s .= ' cx="'.$this->cx.'"';
        $s .= ' cy="'.$this->cy.'"';
        $s .= ' rx="'.$this->rx.'"';
        $s .= ' ry="'.$this->ry.'"';

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