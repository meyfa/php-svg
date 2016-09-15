<?php

namespace JangoBrick\SVG\Nodes;

use JangoBrick\SVG\SVGRenderingHelper;

abstract class SVGNode
{
    protected $parent;
    protected $styles;
    protected $attributes;

    public function __construct()
    {
        $this->styles = [];
        $this->attributes = [];
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

    public function getAttribute($name)
    {
        return isset($this->attributes[$name]) ? $this->attributes[$name] : null;
    }

    public function setAttribute($name, $value)
    {
        $this->attributes[$name] = $value;
    }

    public function removeAttribute($name)
    {
        unset($this->attributes[$name]);
    }

    protected function addAttributesToXMLString($xmlString)
    {
        if (!empty($this->attributes)) {
            foreach ($this->attributes as $attributeName => $attributeValue) {
                $xmlString .= ' '.$attributeName.'="'.$attributeValue.'"';
            }
        }
    }

    abstract public function toXMLString();

    abstract public function draw(SVGRenderingHelper $rh, $scaleX, $scaleY, $offsetX = 0, $offsetY = 0);

    public function __toString()
    {
        return $this->toXMLString();
    }
}
