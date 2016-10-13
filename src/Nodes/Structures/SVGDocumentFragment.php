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
        parent::__construct('svg');

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

    public function getSerializableAttributes()
    {
        $attrs = parent::getSerializableAttributes();

        if ($this->root) {
            $attrs['xmlns'] = 'http://www.w3.org/2000/svg';
            foreach ($this->namespaces as $namespace => $uri) {
                if (substr($namespace, 0, 6) !== 'xmlns:') {
                    $namespace = 'xmlns:'.$namespace;
                }
                $attrs[$namespace] = $uri;
            }
        } else {
            if ($this->x != 0) {
                $attrs['x'] = $this->x;
            }
            if ($this->y != 0) {
                $attrs['x'] = $this->y;
            }
        }

        if ($this->width != '100%') {
            $attrs['width'] = $this->width;
        }
        if ($this->height != '100%') {
            $attrs['height'] = $this->height;
        }

        return $attrs;
    }

    public function getSerializableStyles()
    {
        if (!$this->root) {
            return parent::getSerializableStyles();
        }

        $styles = array();
        // filter styles to not include initial/default ones
        foreach ($this->styles as $style => $value) {
            if (!isset(self::$initialStyles[$style]) || self::$initialStyles[$style] !== $value) {
                $styles[$style] = $value;
            }
        }

        return $styles;
    }
}
