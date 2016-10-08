<?php

namespace JangoBrick\SVG\Nodes;

use JangoBrick\SVG\Rasterization\SVGRasterizer;

abstract class SVGNode
{
    protected $parent;
    protected $styles;
    protected $attributes;

    public function __construct()
    {
        $this->styles = array();
        $this->attributes = array();
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function getStyle($name)
    {
        return isset($this->styles[$name]) ? $this->styles[$name] : null;
    }

    public function setStyle($name, $value)
    {
        $this->styles[$name] = $value;
        return $this;
    }

    public function removeStyle($name)
    {
        unset($this->styles[$name]);
        return $this;
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
        return $this;
    }

    public function removeAttribute($name)
    {
        unset($this->attributes[$name]);
        return $this;
    }

    protected function addStylesToXMLString(&$xmlString)
    {
        if (empty($this->styles)) {
            return;
        }

        $xmlString .= ' style="';
        $prependSemicolon = false;
        foreach ($this->styles as $style => $value) {
            if ($prependSemicolon) {
                $xmlString .= '; ';
            }
            $prependSemicolon = true;
            $xmlString .= $style.': '.$value;
        }
        $xmlString .= '"';
    }

    protected function addAttributesToXMLString(&$xmlString)
    {
        if (empty($this->attributes)) {
            return;
        }

        foreach ($this->attributes as $attributeName => $attributeValue) {
            $xmlString .= ' '.$attributeName.'="'.$attributeValue.'"';
        }
    }

    abstract public function toXMLString();

    public function __toString()
    {
        return $this->toXMLString();
    }

    abstract public function rasterize(SVGRasterizer $rasterizer);
}
