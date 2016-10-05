<?php

namespace JangoBrick\SVG;

use JangoBrick\SVG\Nodes\Shapes\SVGCircle;
use JangoBrick\SVG\Nodes\Shapes\SVGEllipse;
use JangoBrick\SVG\Nodes\Shapes\SVGLine;
use JangoBrick\SVG\Nodes\Shapes\SVGPath;
use JangoBrick\SVG\Nodes\Shapes\SVGPolygon;
use JangoBrick\SVG\Nodes\Shapes\SVGPolyline;
use JangoBrick\SVG\Nodes\Shapes\SVGRect;
use JangoBrick\SVG\Nodes\Structures\SVGDocumentFragment;
use JangoBrick\SVG\Nodes\Structures\SVGGroup;

class SVGImage
{
    private $document;

    public function __construct($width, $height, $namespaces = array('xmlns' => 'http://www.w3.org/2000/svg'))
    {
        $this->document   = new SVGDocumentFragment(true, $width, $height, $namespaces); // root doc
    }

    /**
     * @return SVGDocumentFragment
     */
    public function getDocument()
    {
        return $this->document;
    }

    public function toXMLString()
    {
        $s  = '<?xml version="1.0" encoding="utf-8"?>';
        $s .= $this->document;

        return $s;
    }

    public function toRasterImage($width, $height)
    {
        $out = imagecreatetruecolor($width, $height);

        imagealphablending($out, true);
        imagesavealpha($out, true);

        imagefill($out, 0, 0, 0x7F000000);

        $rh = new SVGRenderingHelper($out, $width, $height);

        $scaleX = $width / $this->document->getWidth();
        $scaleY = $height / $this->document->getHeight();
        $this->document->draw($rh, $scaleX, $scaleY);

        return $out;
    }

    public function __toString()
    {
        return $this->toXMLString();
    }

    public static function fromString($string)
    {
        return self::parse(simplexml_load_string($string));
    }

    public static function fromFile($file)
    {
        return self::parse(simplexml_load_file($file));
    }

    // Give it a SimpleXML element of type <svg>, it will return an SVGImage.
    private static function parse($svg)
    {
        $image = new self($svg['width'], $svg['height']);
        $doc   = $image->getDocument();

        $children = $svg->children();
        foreach ($children as $child) {
            $childNode = self::parseNode($child);
            if (!$childNode) {
                continue;
            }
            $doc->addChild($childNode);
        }

        return $image;
    }

    // Expects a SimpleXML element as the only parameter.
    // It will parse the node and any possible children and return an instance
    // of the appropriate class (e.g. SVGRect or SVGGroup).
    private static function parseNode($node)
    {
        $type = $node->getName();

        if ($type === 'g') {
            $element = new SVGGroup();

            $children = $node->children();
            foreach ($children as $child) {
                $element->addChild(self::parseNode($child));
            }
        } elseif ($type === 'rect') {
            $w = isset($node['width']) ? $node['width'] : 0;
            $h = isset($node['height']) ? $node['height'] : 0;
            $x = isset($node['x']) ? $node['x'] : 0;
            $y = isset($node['y']) ? $node['y'] : 0;

            $element = new SVGRect($x, $y, $w, $h);
        } elseif ($type === 'circle') {
            $cx = isset($node['cx']) ? $node['cx'] : 0;
            $cy = isset($node['cy']) ? $node['cy'] : 0;
            $r  = isset($node['r']) ? $node['r'] : 0;

            $element = new SVGCircle($cx, $cy, $r);
        } elseif ($type === 'ellipse') {
            $cx = isset($node['cx']) ? $node['cx'] : 0;
            $cy = isset($node['cy']) ? $node['cy'] : 0;
            $rx = isset($node['rx']) ? $node['rx'] : 0;
            $ry = isset($node['ry']) ? $node['ry'] : 0;

            $element = new SVGEllipse($cx, $cy, $rx, $ry);
        } elseif ($type === 'line') {
            $x1 = isset($node['x1']) ? $node['x1'] : 0;
            $y1 = isset($node['y1']) ? $node['y1'] : 0;
            $x2 = isset($node['x2']) ? $node['x2'] : 0;
            $y2 = isset($node['y2']) ? $node['y2'] : 0;

            $element = new SVGLine($x1, $y1, $x2, $y2);
        } elseif ($type === 'polygon' || $type === 'polyline') {
            $element = $type === 'polygon' ? new SVGPolygon() : new SVGPolyline();

            $points = isset($node['points']) ? preg_split('/[\\s,]+/', $node['points']) : array();
            for ($i = 0, $n = floor(count($points) / 2); $i < $n; ++$i) {
                $element->addPoint($points[$i * 2], $points[$i * 2 + 1]);
            }
        } elseif ($type === 'path') {
            $d       = isset($node['d']) ? $node['d'] : '';
            $element = new SVGPath($d);
        }

        if (!isset($element)) {
            return false;
        }

        $attributes        = $node->attributes();
        $ignoredAttributes = array(
            'x', 'y', 'width', 'height',
            'x1', 'y1', 'x2', 'y2',
            'cx', 'cy', 'r', 'rx', 'ry',
            'points',
            'style',
        );
        foreach ($attributes as $attribute => $value) {
            if (in_array($attribute, $ignoredAttributes)) {
                continue;
            }
            $element->setStyle($attribute, $value);
        }

        if (isset($node['style'])) {
            $styles = self::parseStyles($node['style']);
            foreach ($styles as $style => $value) {
                $element->setStyle($style, $value);
            }
        }

        return $element;
    }

    // Basic style attribute parsing function.
    // Takes strings like 'fill: #000; stroke: none' and returns associative
    // array like: ['fill' => '#000', 'stroke' => 'none']
    private static function parseStyles($styles)
    {
        $styles = preg_split('/\s*;\s*/', $styles);
        $arr    = array();

        foreach ($styles as $style) {
            if (($style = trim($style)) === '') {
                continue;
            }
            $style_spl          = preg_split('/\s*:\s*/', $style);
            $arr[$style_spl[0]] = $style_spl[1];
        }

        return $arr;
    }
}
