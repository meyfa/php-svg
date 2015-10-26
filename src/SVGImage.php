<?php

class SVGImage {

    private $namespaces;
    private $document;



    public function __construct($width, $height) {
        $this->namespaces = array();
        $this->document = new SVGDocumentFragment(true, $width, $height); // root doc
    }





    public function getDocument() {
        return $this->document;
    }





    public function toXMLString() {

        $s  = '<?xml version="1.0" encoding="utf-8"?>';
        $s .= $this->document;

        return $s;

    }





    public function toRasterImage($width, $height) {

        $out = imagecreatetruecolor($width, $height);

        imagealphablending($out, true);
        imagesavealpha($out, true);

        imagefill($out, 0, 0, 0x7c000000);

        $rh = new SVGRenderingHelper($out, $width, $height);

        $scaleX = $width / $this->document->getWidth();
        $scaleY = $height / $this->document->getHeight();
        $this->document->draw($rh, $scaleX, $scaleY, 0, 0);

        return $out;

    }





    public function __toString() {
        return $this->toXMLString();
    }

}