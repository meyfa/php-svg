<?php

namespace Jangobrick\SVG\Nodes;

use Jangobrick\SVG\SVGRenderingHelper;

abstract class SVGNode
{
    protected $parent;
    protected $styles;

    public function __construct()
    {
        $this->styles = [];
    }

    public function getStyle($name)
    {
        return isset($this->styles[$name]) ? $this->styles[$name] : null;
    }

    public function setStyle($name, $value)
    {
        $this->styles[$name] = $value;
    }

    public function removeStyle($name)
    {
        unset($this->styles[$name]);
    }

    public function getComputedStyle($name)
    {
        $style = null;

        if (isset($this->styles[$name])) {
            $style = $this->styles[$name];
        }

        if (($style === null || $style === 'inherit') && isset($this->parent)) {
            return $this->parent->getComputedStyle($name);
        }

        // 'inherit' is not what we want. Either get the real style, or
        // nothing at all.
        return $style !== 'inherit' ? $style : null;
    }

    abstract public function toXMLString();

    abstract public function draw(SVGRenderingHelper $rh, $scaleX, $scaleY, $offsetX = 0, $offsetY = 0);

    public function __toString()
    {
        return $this->toXMLString();
    }
}
