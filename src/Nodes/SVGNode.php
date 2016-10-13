<?php

namespace JangoBrick\SVG\Nodes;

use JangoBrick\SVG\Rasterization\SVGRasterizer;

abstract class SVGNode
{
    private $name;
    protected $parent;
    protected $styles;
    protected $attributes;

    public function __construct($name)
    {
        $this->name       = $name;
        $this->styles     = array();
        $this->attributes = array();
    }

    /**
     * @SuppressWarnings("unused")
     */
    public static function constructFromAttributes($attrs)
    {
        throw new \Exception(get_called_class().' does not implement '.__FUNCTION__.'!');
    }

    public function getName()
    {
        return $this->name;
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

    public function getSerializableAttributes()
    {
        return $this->attributes;
    }

    public function getSerializableStyles()
    {
        return $this->styles;
    }

    abstract public function rasterize(SVGRasterizer $rasterizer);
}
