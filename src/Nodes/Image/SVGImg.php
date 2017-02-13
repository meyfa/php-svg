<?php

/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 2/3/17
 */

namespace JangoBrick\SVG\Nodes\Image;

use JangoBrick\SVG\Nodes\SVGNode;
use JangoBrick\SVG\Rasterization\Renderers\SVGImgRenderer;
use JangoBrick\SVG\Rasterization\SVGRasterizer;

class SVGImg extends SVGNode
{
    const TAG_NAME = 'image';

    /**
     * @param $href
     * @param null $x
     * @param null $y
     * @param null $width
     * @param null $height
     */
    public function __construct($href, $x = null, $y = null, $width = null, $height = null)
    {
        parent::__construct();

        $this->setAttribute('xlink:href', $href);
        $this->setAttributeOptional('x', $x);
        $this->setAttributeOptional('y', $y);
        $this->setAttributeOptional('width', $width);
        $this->setAttributeOptional('height', $height);
    }

    /**
     * @return string The image's path or url
     */
    public function getHref()
    {
        if (!empty($href = $this->getAttribute('xlink:href'))) {
            return $href;
        } else {
            return $this->getAttribute('href');
        }
    }

    /**
     * Sets the image's path or url.
     *
     * @param string $href
     *
     * @return $this This node instance, for call chaining
     */
    public function setHref($href)
    {
        return $this->setAttribute('xlink:href', $href);
    }

    /**
     * @return string The y coordinate
     */
    public function getY()
    {
        return $this->getAttribute('y');
    }

    /**
     * @return string The x coordinate
     */
    public function getX()
    {
        return $this->getAttribute('x');
    }

    /**
     * Sets the x coordinate.
     *
     * @param string $x The new coordinate
     *
     * @return $this This node instance, for call chaining
     */
    public function setX($x)
    {
        return $this->setAttribute('x', $x);
    }

    /**
     * @return string The width
     */
    public function getWidth()
    {
        return $this->getAttribute('width');
    }

    /**
     * Sets the width.
     *
     * @param string $width The new width
     *
     * @return $this This node instance, for call chaining
     */
    public function setWidth($width)
    {
        return $this->setAttribute('width', $width);
    }

    /**
     * @return string The height
     */
    public function getHeight()
    {
        return $this->getAttribute('height');
    }

    /**
     * Sets the height.
     *
     * @param string $height The new height
     *
     * @return $this This node instance, for call chaining
     */
    public function setHeight($height)
    {
        return $this->setAttribute('height', $height);
    }

    public function rasterize(SVGRasterizer $rasterizer)
    {
        if ($this->getComputedStyle('display') === 'none') {
            return;
        }

        $visibility = $this->getComputedStyle('visibility');
        if ($visibility === 'hidden' || $visibility === 'collapse') {
            return;
        }
        $rasterizer->render('image', array(
            'href' => $this->getHref(),
            'x' => $this->getX(),
            'y' => $this->getY(),
            'width' => $this->getWidth(),
            'height' => $this->getHeight(),
        ), $this);
    }
}
