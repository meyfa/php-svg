<?php

namespace JangoBrick\SVG\Nodes\Shapes;

use JangoBrick\SVG\Nodes\SVGNode;
use JangoBrick\SVG\Rasterization\SVGRasterizer;

/**
 * Represents the SVG tag 'ellipse'.
 * Has the special attributes cx, cy, rx, ry.
 */
class SVGEllipse extends SVGNode
{
    const TAG_NAME = 'ellipse';

    /**
     * @var string $cx The center's x coordinate.
     * @var string $cy The center's y coordinate.
     * @var string $rx The radius along the x-axis.
     * @var string $ry The radius along the y-axis.
     */
    private $cx, $cy, $rx, $ry;

    /**
     * @param string $cx The center's x coordinate.
     * @param string $cy The center's y coordinate.
     * @param string $rx The radius along the x-axis.
     * @param string $ry The radius along the y-axis.
     */
    public function __construct($cx, $cy, $rx, $ry)
    {
        parent::__construct();

        $this->cx = $cx;
        $this->cy = $cy;
        $this->rx = $rx;
        $this->ry = $ry;
    }

    /**
     * @inheritDoc
     * @SuppressWarnings("NPath")
     */
    public static function constructFromAttributes($attrs)
    {
        $cx = isset($attrs['cx']) ? $attrs['cx'] : '';
        $cy = isset($attrs['cy']) ? $attrs['cy'] : '';
        $rx = isset($attrs['rx']) ? $attrs['rx'] : '';
        $ry = isset($attrs['ry']) ? $attrs['ry'] : '';

        return new self($cx, $cy, $rx, $ry);
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
     * @return string The radius along the x-axis.
     */
    public function getRadiusX()
    {
        return $this->rx;
    }

    /**
     * Sets the radius along the x-axis.
     *
     * @param string $rx The new radius.
     *
     * @return $this This node instance, for call chaining.
     */
    public function setRadiusX($rx)
    {
        $this->rx = $rx;
        return $this;
    }

    /**
     * @return string The radius along the y-axis.
     */
    public function getRadiusY()
    {
        return $this->ry;
    }

    /**
     * Sets the radius along the y-axis.
     *
     * @param string $ry The new radius.
     *
     * @return $this This node instance, for call chaining.
     */
    public function setRadiusY($ry)
    {
        $this->ry = $ry;
        return $this;
    }



    public function getSerializableAttributes()
    {
        $attrs = parent::getSerializableAttributes();

        $attrs['cx'] = $this->cx;
        $attrs['cy'] = $this->cy;
        $attrs['rx'] = $this->rx;
        $attrs['ry'] = $this->ry;

        return $attrs;
    }



    public function rasterize(SVGRasterizer $rasterizer)
    {
        $rasterizer->render('ellipse', array(
            'cx'    => $this->cx,
            'cy'    => $this->cy,
            'rx'    => $this->rx,
            'ry'    => $this->ry,
        ), $this);
    }
}
