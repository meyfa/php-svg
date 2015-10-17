<?php

class SVGRect extends SVGNode {

    public function __construct($x, $y, $width, $height) {
        $this->x = $x;
        $this->y = $y;
        $this->width = $width;
        $this->height = $height;
    }





    public function toXMLString() {

        $s  = '<rect';

        $s .= ' x="'.$this->x.'"';
        $s .= ' y="'.$this->y.'"';
        $s .= ' width="'.$this->width.'"';
        $s .= ' height="'.$this->height.'"';

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