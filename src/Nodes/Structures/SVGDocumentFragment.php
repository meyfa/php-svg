<?php

namespace JangoBrick\SVG\Nodes\Structures;

use JangoBrick\SVG\Nodes\SVGNodeContainer;

class SVGDocumentFragment extends SVGNodeContainer
{
    private static $initialStyles = array(
        'fill'          => '#000000',
        'stroke'        => 'none',
        'stroke-width'  => 1,
        'opacity'       => 1,
    );

    protected $x, $y, $width, $height;
    private $root;
    private $namespaces;

    public function __construct($root = false, $width = '100%', $height = '100%', array $namespaces = array())
    {
        parent::__construct();

        $this->root = (bool) $root;
        $this->namespaces = $namespaces;

        $this->width  = $width;
        $this->height = $height;

        foreach (self::$initialStyles as $style => $value) {
            $this->setStyle($style, $value);
        }
    }

    public function isRoot()
    {
        return $this->root;
    }

    public function getWidth()
    {
        return $this->width;
    }

    public function setWidth($width)
    {
        $this->width = $width;
        return $this;
    }

    public function getHeight()
    {
        return $this->height;
    }

    public function setHeight($height)
    {
        $this->height = $height;
        return $this;
    }

    public function toXMLString()
    {
        $s  = '<svg';

        if ($this->root) {
            $s .= ' xmlns="http://www.w3.org/2000/svg"';
            foreach ($this->namespaces as $namespace => $uri) {
                $s .= ' '. $namespace.'="'.$uri.'"';
            }
        } else {
            if ($this->x != 0) {
                $s .= ' x="'.$this->x.'"';
            }
            if ($this->y != 0) {
                $s .= ' y="'.$this->y.'"';
            }
        }

        if ($this->width != '100%') {
            $s .= ' width="'.$this->width.'"';
        }
        if ($this->height != '100%') {
            $s .= ' height="'.$this->height.'"';
        }

        if ($this->root) {
            $styles = array();
            // filter styles to not include initial/default ones
            foreach ($this->styles as $style => $value) {
                if (!isset(self::$initialStyles[$style]) || self::$initialStyles[$style] !== $value) {
                    $styles[$style] = $value;
                }
            }
        } else {
            $styles = $this->styles;
        }

        if (!empty($styles)) {
            $s .= ' style="';
            foreach ($styles as $style => $value) {
                $s .= $style.': '.$value.'; ';
            }
            $s .= '"';
        }

        $this->addAttributesToXMLString($s);

        $s .= '>';

        for ($i = 0, $n = $this->countChildren(); $i < $n; ++$i) {
            $child = $this->getChild($i);
            $s .= $child->toXMLString();
        }

        $s .= '</svg>';

        return $s;
    }
}
