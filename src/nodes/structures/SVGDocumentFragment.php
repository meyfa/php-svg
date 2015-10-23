<?php

class SVGDocumentFragment extends SVGNodeContainer {

    private static $INITIAL_STYLES = array(
        'fill' => '#000000',
        'stroke' => 'none',
        'stroke-width' => 1
    );

    private $root;



    public function __construct($root = false, $width = "100%", $height = "100%") {

        parent::__construct();

        $this->root = !!$root;

        $this->width = $width;
        $this->height = $height;

        foreach (self::$INITIAL_STYLES as $style => $value) {
            $this->setStyle($style, $value);
        }

    }





    public function isRoot() {
        return $this->root;
    }





    public function getWidth() {
        return $this->width;
    }

    public function setWidth($width) {
        $this->width = $width;
    }



    public function getHeight() {
        return $this->height;
    }

    public function setHeight($height) {
        $this->height = $height;
    }





    public function toXMLString() {

        $s  = '<svg';

        if ($this->root) {
            $s .= ' xmlns="http://www.w3.org/2000/svg"';
        } else {
            if ($this->x != 0)
                $s .= ' x="'.$this->x.'"';
            if ($this->y != 0)
                $s .= ' y="'.$this->y.'"';
        }

        if ($this->width != "100%")
            $s .= ' width="'.$this->width.'"';
        if ($this->height != "100%")
            $s .= ' height="'.$this->height.'"';

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

        $s .= '</svg>';

        return $s;

    }





    public function draw($image, $imageWidth, $imageHeight, $scaleX, $scaleY, $offsetX = 0, $offsetY = 0) {

        $offsetX += $this->x;
        $offsetY += $this->y;

        for ($i=0, $n=$this->countChildren(); $i<$n; $i++) {
            $child = $this->getChild($i);
            $child->draw($image, $imageWidth, $imageHeight, $scaleX, $scaleY, $offsetX, $offsetY);
        }

    }

}