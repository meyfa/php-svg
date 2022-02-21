<?php

namespace SVG\Nodes\Shapes;

use SVG\Nodes\SVGNodeContainer;
use SVG\Rasterization\SVGRasterizer;
use SVG\Rasterization\Transform\TransformParser;

/**
 * Represents the SVG tag 'path'.
 */
class SVGPath extends SVGNodeContainer
{
    const TAG_NAME = 'path';

    /**
     * @param string|null $d The path description.
     */
    public function __construct($d = null)
    {
        parent::__construct();

        $this->setAttribute('d', $d);
    }

    /**
     * @return string The path description string.
     */
    public function getDescription()
    {
        return $this->getAttribute('d');
    }

    /**
     * Sets the path description string.
     *
     * @param string $d The new description.
     *
     * @return $this This node instance, for call chaining.
     */
    public function setDescription($d)
    {
        return $this->setAttribute('d', $d);
    }

    /**
     * @inheritdoc
     */
    public function rasterize(SVGRasterizer $rasterizer)
    {
        if ($this->getComputedStyle('display') === 'none') {
            return;
        }

        $visibility = $this->getComputedStyle('visibility');
        if ($visibility === 'hidden' || $visibility === 'collapse') {
            return;
        }

        $d = $this->getDescription();
        if (!isset($d)) {
            return;
        }

        $commands = $rasterizer->getPathParser()->parse($d);
        $subpaths = $rasterizer->getPathApproximator()->approximate($commands);

        TransformParser::parseTransformString($this->getAttribute('transform'), $rasterizer->pushTransform());

        $rasterizer->render('path', array(
            'segments'  => $subpaths,
            'fill-rule' => strtolower(trim($this->getComputedStyle('fill-rule') ?: 'nonzero'))
        ), $this);

        $rasterizer->popTransform();
    }
}
