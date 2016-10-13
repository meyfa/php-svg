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
use JangoBrick\SVG\Rasterization\SVGRasterizer;
use JangoBrick\SVG\Writing\SVGWriter;

class SVGImage
{
    private $document;

    public function __construct($width, $height, array $namespaces = array())
    {
        $this->document = new SVGDocumentFragment(true, $width, $height, $namespaces);
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
        $writer = new SVGWriter();
        $writer->writeNode($this->document);

        return $writer->getString();
    }

    public function toRasterImage($width, $height)
    {
        $docWidth  = $this->document->getWidth();
        $docHeight = $this->document->getHeight();

        $rasterizer = new SVGRasterizer($docWidth, $docHeight, $width, $height);
        $this->document->rasterize($rasterizer);

        return $rasterizer->getImage();
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
    private static function parse(\SimpleXMLElement $element)
    {
        $image = new self($element['width'], $element['height']);
        $doc   = $image->getDocument();

        $children = $element->children();
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
    private static function parseNode(\SimpleXMLElement $element)
    {
        $type = $element->getName();

        if ($type === 'g') {
            $node = new SVGGroup();

            $children = $element->children();
            foreach ($children as $child) {
                $node->addChild(self::parseNode($child));
            }
        } elseif ($type === 'rect') {
            $w = isset($element['width']) ? $element['width'] : 0;
            $h = isset($element['height']) ? $element['height'] : 0;
            $x = isset($element['x']) ? $element['x'] : 0;
            $y = isset($element['y']) ? $element['y'] : 0;

            $node = new SVGRect($x, $y, $w, $h);
        } elseif ($type === 'circle') {
            $cx = isset($element['cx']) ? $element['cx'] : 0;
            $cy = isset($element['cy']) ? $element['cy'] : 0;
            $r  = isset($element['r']) ? $element['r'] : 0;

            $node = new SVGCircle($cx, $cy, $r);
        } elseif ($type === 'ellipse') {
            $cx = isset($element['cx']) ? $element['cx'] : 0;
            $cy = isset($element['cy']) ? $element['cy'] : 0;
            $rx = isset($element['rx']) ? $element['rx'] : 0;
            $ry = isset($element['ry']) ? $element['ry'] : 0;

            $node = new SVGEllipse($cx, $cy, $rx, $ry);
        } elseif ($type === 'line') {
            $x1 = isset($element['x1']) ? $element['x1'] : 0;
            $y1 = isset($element['y1']) ? $element['y1'] : 0;
            $x2 = isset($element['x2']) ? $element['x2'] : 0;
            $y2 = isset($element['y2']) ? $element['y2'] : 0;

            $node = new SVGLine($x1, $y1, $x2, $y2);
        } elseif ($type === 'polygon' || $type === 'polyline') {
            $node = ($type === 'polygon') ? new SVGPolygon() : new SVGPolyline();

            if (isset($element['points'])) {
                $points = preg_split('/[\\s,]+/', $element['points']);
                for ($i = 0, $n = count($points); $i < $n; $i += 2) {
                    $node->addPoint($points[$i], $points[$i + 1]);
                }
            }
        } elseif ($type === 'path') {
            $d    = isset($element['d']) ? $element['d'] : '';
            $node = new SVGPath($d);
        }

        if (!isset($node)) {
            return false;
        }

        $attributes        = $element->attributes();
        $ignoredAttributes = array(
            'x', 'y', 'width', 'height',
            'x1', 'y1', 'x2', 'y2',
            'cx', 'cy', 'r', 'rx', 'ry',
            'points',
            'd',
            'style',
        );
        foreach ($attributes as $attribute => $value) {
            if (in_array($attribute, $ignoredAttributes)) {
                continue;
            }
            $node->setStyle($attribute, $value);
        }

        if (isset($element['style'])) {
            $styles = self::parseStyles($element['style']);
            foreach ($styles as $style => $value) {
                $node->setStyle($style, $value);
            }
        }

        return $node;
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
            $split          = preg_split('/\s*:\s*/', $style);
            $arr[$split[0]] = $split[1];
        }

        return $arr;
    }
}
