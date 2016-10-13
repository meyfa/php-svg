<?php

namespace JangoBrick\SVG\Reading;

use JangoBrick\SVG\SVGImage;
use JangoBrick\SVG\Nodes\SVGNode;
use JangoBrick\SVG\Nodes\SVGNodeContainer;

class SVGReader
{
    /**
     * Associative array mapping node names (e.g. 'rect') to fully-qualified
     * class names (e.g. 'JangoBrick\\SVG\\Nodes\\Shapes\\SVGRect').
     */
    private static $nodeTypes = array(
        'svg'       => 'JangoBrick\\SVG\\Nodes\\Structures\\SVGDocumentFragment',
        'g'         => 'JangoBrick\\SVG\\Nodes\\Structures\\SVGGroup',
        'rect'      => 'JangoBrick\\SVG\\Nodes\\Shapes\\SVGRect',
        'circle'    => 'JangoBrick\\SVG\\Nodes\\Shapes\\SVGCircle',
        'ellipse'   => 'JangoBrick\\SVG\\Nodes\\Shapes\\SVGEllipse',
        'line'      => 'JangoBrick\\SVG\\Nodes\\Shapes\\SVGLine',
        'polygon'   => 'JangoBrick\\SVG\\Nodes\\Shapes\\SVGPolygon',
        'polyline'  => 'JangoBrick\\SVG\\Nodes\\Shapes\\SVGPolyline',
        'path'      => 'JangoBrick\\SVG\\Nodes\\Shapes\\SVGPath',
    );
    /**
     * Array of attributes to ignore because they are dealt with elsewhere.
     */
    private static $ignoredAttributes = array(
        'x', 'y', 'width', 'height',
        'x1', 'y1', 'x2', 'y2',
        'cx', 'cy', 'r', 'rx', 'ry',
        'points', 'd',
        'style',
    );
    /**
     * Array of attributes that are to be interpreted as styles.
     * Comes from https://www.w3.org/TR/SVG/styling.html.
     */
    private static $styleAttributes = array(
        // DEFINED IN BOTH CSS2 AND SVG
        // font properties
        'font', 'font-family', 'font-size', 'font-size-adjust', 'font-stretch',
        'font-style', 'font-variant', 'font-weight',
        // text properties
        'direction', 'letter-spacing', 'word-spacing', 'text-decoration',
        'unicode-bidi',
        // other properties for visual media
        'clip', 'color', 'cursor', 'display', 'overflow', 'visibility',
        // NOT DEFINED IN CSS2
        // clipping, masking and compositing properties
        'clip-path', 'clip-rule', 'mask', 'opacity',
        // filter effects properties
        'enable-background', 'filter', 'flood-color', 'flood-opacity',
        'lighting-color',
        // gradient properties
        'stop-color', 'stop-opacity',
        // interactivity properties
        'pointer-events',
        // color and painting properties
        'color-interpolation', 'color-interpolation-filters', 'color-profile',
        'color-rendering', 'fill', 'fill-opacity', 'fill-rule',
        'image-rendering', 'marker', 'marker-end', 'marker-mid', 'marker-start',
        'shape-rendering', 'stroke', 'stroke-dasharray', 'stroke-dashoffset',
        'stroke-linecap', 'stroke-linejoin', 'stroke-miterlimit',
        'stroke-opacity', 'stroke-width', 'text-rendering',
        // text properties
        'alignment-base', 'baseline-shift', 'dominant-baseline',
        'glyph-orientation-horizontal', 'glyph-orientation-vertical', 'kerning',
        'text-anchor', 'writing-mode',
    );

    public function parseString($string)
    {
        $xml = simplexml_load_string($string);
        return $this->parseXML($xml);
    }

    public function parseFile($filename)
    {
        $xml = simplexml_load_file($filename);
        return $this->parseXML($xml);
    }

    public function parseXML(\SimpleXMLElement $xml)
    {
        $name = $xml->getName();
        if ($name !== 'svg') {
            return false;
        }

        $dim = $this->getDimensions($xml);
        $img = new SVGImage($dim[0], $dim[1]);

        $doc = $img->getDocument();

        $this->applyAttributes($doc, $xml);
        $this->applyStyles($doc, $xml);

        $this->addChildren($doc, $xml);

        return $img;
    }

    private function getDimensions(\SimpleXMLElement $svgXml)
    {
        return array(
            floatval($svgXml['width']),
            floatval($svgXml['height']),
        );
    }

    private function applyAttributes(SVGNode $node, \SimpleXMLElement $xml)
    {
        foreach ($xml->attributes() as $key => $value) {
            if (in_array($key, self::$ignoredAttributes)) {
                continue;
            }
            if (in_array($key, self::$styleAttributes)) {
                $node->setStyle($key, $value);
                continue;
            }
            $node->setAttribute($key, $value);
        }
    }

    private function applyStyles(SVGNode $node, \SimpleXMLElement $xml)
    {
        if (!isset($xml['style'])) {
            return;
        }

        $styles = $this->parseStyles($xml['style']);
        foreach ($styles as $key => $value) {
            $node->setStyle($key, $value);
        }
    }

    private function parseStyles($string)
    {
        $declarations = preg_split('/\s*;\s*/', $string);

        $styles = array();

        foreach ($declarations as $declaration) {
            $declaration = trim($declaration);
            if ($declaration === '') {
                continue;
            }
            $split             = preg_split('/\s*:\s*/', $declaration);
            $styles[$split[0]] = $split[1];
        }

        return $styles;
    }

    private function addChildren(SVGNodeContainer $node, \SimpleXMLElement $xml)
    {
        foreach ($xml->children() as $child) {
            $childNode = $this->parseNode($child);
            if (!$childNode) {
                continue;
            }
            $node->addChild($childNode);
        }
    }

    private function parseNode(\SimpleXMLElement $xml)
    {
        $type = $xml->getName();

        if (!isset(self::$nodeTypes[$type])) {
            return false;
        }

        $call = array(self::$nodeTypes[$type], 'constructFromAttributes');
        $node = call_user_func($call, $xml);

        $this->applyAttributes($node, $xml);
        $this->applyStyles($node, $xml);

        if ($node instanceof SVGNodeContainer) {
            $this->addChildren($node, $xml);
        }

        return $node;
    }
}
