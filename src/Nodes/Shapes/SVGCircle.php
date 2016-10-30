<?php

namespace JangoBrick\SVG\Nodes\Shapes;

use JangoBrick\SVG\Nodes\SVGNode;
use JangoBrick\SVG\Rasterization\SVGRasterizer;

/**
 * Represents the SVG tag 'circle'.
 * Has the special attributes cx, cy, r.
 */
class SVGCircle extends SVGNode
{
    /**
     * @var string $cx The center's x coordinate.
     * @var string $cy The center's y coordinate.
     * @var string $rx The radius.
     */
    private $cx, $cy, $r;

    /**
     * @param string $cx The center's x coordinate.
     * @param string $cy The center's y coordinate.
     * @param string $rx The radius.
     */
    public function __construct($cx, $cy, $r)
    {
        parent::__construct('circle');

        $this->cx = $cx;
        $this->cy = $cy;
        $this->r  = $r;
    }

    public static function constructFromAttributes($attrs)
    {
        $cx = isset($attrs['cx']) ? $attrs['cx'] : '';
        $cy = isset($attrs['cy']) ? $attrs['cy'] : '';
        $r  = isset($attrs['r']) ? $attrs['r'] : '';

        return new self($cx, $cy, $r);
    }



    /**
     * @return string The center's x coordinate.
     */
    public function getCenterX()
    {
        return $this->cx;
    }

    /**
     * Sets the center's x coordinate.
     *
     * @param string $cx The new coordinate.
     *
     * @return $this This node instance, for call chaining.
     */
    public function setCenterX($cx)
    {
        $this->cx = $cx;
        return $this;
    }

    /**
     * @return string The center's y coordinate.
     */
    public function getCenterY()
    {
        return $this->cy;
    }

    /**
     * Sets the center's y coordinate.
     *
     * @param string $cy The new coordinate.
     *
     * @return $this This node instance, for call chaining.
     */
    public function setCenterY($cy)
    {
        $this->cy = $cy;
        return $this;
    }



    /**
     * @return string The radius.
     */
    public function getRadius()
    {
        return $this->r;
    }

    /**
     * Sets the radius.
     *
     * @param string $r The new radius.
     *
     * @return $this This node instance, for call chaining.
     */
    public function setRadius($r)
    {
        $this->r = $r;
        return $this;
    }



    public function getSerializableAttributes()
    {
        $attrs = parent::getSerializableAttributes();

        $attrs['cx'] = $this->cx;
        $attrs['cy'] = $this->cy;
        $attrs['r']  = $this->r;

        return $attrs;
    }



    public function rasterize(SVGRasterizer $rasterizer)
    {
        $rasterizer->render('ellipse', array(
            'cx'    => $this->cx,
            'cy'    => $this->cy,
            'rx'    => $this->r,
            'ry'    => $this->r,
        ), $this);
    }
}
