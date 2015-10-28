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





    public static function fromString($string) {
        return self::parse(simplexml_load_string($string));
    }

    public static function fromFile($file) {
        return self::parse(simplexml_load_file($file));
    }





    // Give it a SimpleXML element of type <svg>, it will return an SVGImage.
    private static function parse($svg) {

        $image = new self($svg['width'], $svg['height']);
        $doc = $image->getDocument();

        $children = $svg->children();
        foreach ($children as $child) {
            $doc->addChild(self::parseNode($child));
        }

        return $image;

    }

    // Expects a SimpleXML element as the only parameter.
    // It will parse the node and any possible children and return an instance
    // of the appropriate class (e.g. SVGRect or SVGGroup).
    private static function parseNode($node) {

        $type = $node->getName();

        if ($type === 'g') {

            $element = new SVGGroup();

            $children = $node->children();
            foreach ($children as $child) {
                $element->addChild(self::parseNode($child));
            }

        } else if ($type === 'rect') {

            $w = isset($node['width']) ? $node['width'] : 0;
            $h = isset($node['height']) ? $node['height'] : 0;
            $x = isset($node['x']) ? $node['x'] : 0;
            $y = isset($node['y']) ? $node['y'] : 0;

            $element = new SVGRect($x, $y, $w, $h);

        } else if ($type === 'circle') {

            $cx = isset($node['cx']) ? $node['cx'] : 0;
            $cy = isset($node['cy']) ? $node['cy'] : 0;
            $r = isset($node['r']) ? $node['r'] : 0;

            $element = new SVGCircle($cx, $cy, $r);

        } else if ($type === 'ellipse') {

            $cx = isset($node['cx']) ? $node['cx'] : 0;
            $cy = isset($node['cy']) ? $node['cy'] : 0;
            $rx = isset($node['rx']) ? $node['rx'] : 0;
            $ry = isset($node['ry']) ? $node['ry'] : 0;

            $element = new SVGEllipse($cx, $cy, $rx, $ry);

        } else if ($type === 'line') {

            $x1 = isset($node['x1']) ? $node['x1'] : 0;
            $y1 = isset($node['y1']) ? $node['y1'] : 0;
            $x2 = isset($node['x2']) ? $node['x2'] : 0;
            $y2 = isset($node['y2']) ? $node['y2'] : 0;

            $element = new SVGLine($x1, $y1, $x2, $y2);

        } else if ($type === 'polygon' || $type === 'polyline') {

            $element = $type === 'polygon' ? new SVGPolygon() : new SVGPolyline();

            $points = isset($node['points']) ? preg_split('/[\\s,]+/', $node['points']) : array();
            for ($i=0, $n=floor(count($points)/2); $i<$n; $i++) {
                $element->addPoint($points[$i*2], $points[$i*2 + 1]);
            }

        }

        return $element;

    }

}
