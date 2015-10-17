<?php

class SVGRect {

    private $x, $y, $width, $height;
    private $styles;



    public function __construct($x, $y, $width, $height) {
        $this->x = $x;
        $this->y = $y;
        $this->width = $width;
        $this->height = $height;
    }





    public function getStyle($name) {
        return $this->styles[$name];
    }

    public function setStyle($name, $value) {
        $this->styles[$name] = $value;
    }

    public function removeStyle($name) {
        unset($this->styles[$name]);
    }





    public function __toString() {

        $s  = '<rect ';

        $s .= 'x="'.$this->x.'" ';
        $s .= 'y="'.$this->y.'" ';
        $s .= 'width="'.$this->width.'" ';
        $s .= 'height="'.$this->height.'" ';

        $s .= 'style="';
        foreach ($this->styles as $style => $value) {
            $s .= $style . ': ' . $value . '; ';
        }
        $s .= '" ';

        $s .= '/>';

        return $s;

    }

}