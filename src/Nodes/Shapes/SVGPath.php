<?php

namespace SVG\Nodes\Shapes;

use SVG\Nodes\SVGNodeContainer;
use SVG\Rasterization\Path\PathParser;
use SVG\Rasterization\SVGRasterizer;
use SVG\Rasterization\Transform\TransformParser;

/**
 * Represents the SVG tag 'path'.
 */
class SVGPath extends SVGNodeContainer
{
    const TAG_NAME = 'path';

    private static $pathParser;

    /**
     * @param string|null $d The path description.
     */
    public function __construct(string $d = null)
    {
        parent::__construct();

        $this->setAttribute('d', $d);
    }

    /**
     * @return string|null The path description string.
     */
    public function getDescription(): ?string
    {
        return $this->getAttribute('d');
    }

    /**
     * Sets the path description string.
     *
     * @param string|null $d The new description.
     *
     * @return $this This node instance, for call chaining.
     */
    public function setDescription(?string $d): SVGPath
    {
        return $this->setAttribute('d', $d);
    }

    /**
     * @inheritdoc
     */
    public function rasterize(SVGRasterizer $rasterizer): void
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

        $commands = self::getPathParser()->parse($d);

        TransformParser::parseTransformString($this->getAttribute('transform'), $rasterizer->pushTransform());

        $rasterizer->render('path', [
            'commands'  => $commands,
            'fill-rule' => strtolower($this->getComputedStyle('fill-rule') ?: 'nonzero')
        ], $this);

        $rasterizer->popTransform();
    }

    private static function getPathParser(): PathParser
    {
        if (!isset(self::$pathParser)) {
            self::$pathParser = new PathParser();
        }
        return self::$pathParser;
    }
}
