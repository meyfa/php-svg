<?php

namespace JangoBrick\SVG\Nodes\Shapes;

use JangoBrick\SVG\Nodes\SVGNode;
use JangoBrick\SVG\Rasterization\SVGRasterizer;

/**
 * Represents the SVG tag 'path'.
 */
class SVGPath extends SVGNode
{
    /** @var string $d The path description. */
    private $d;

    /**
     * @param string $d The path description.
     */
    public function __construct($d)
    {
        parent::__construct('path');

        $this->d = $d;
    }

    public static function constructFromAttributes($attrs)
    {
        $d = isset($attrs['d']) ? $attrs['d'] : '';

        return new self($d);
    }



    public function getSerializableAttributes()
    {
        $attrs = parent::getSerializableAttributes();

        $attrs['d'] = $this->d;

        return $attrs;
    }



    public function rasterize(SVGRasterizer $rasterizer)
    {
        $commands = $rasterizer->getPathParser()->parse($this->d);
        $subpaths = $rasterizer->getPathApproximator()->approximate($commands);

        foreach ($subpaths as $subpath) {
            $rasterizer->render('polygon', array(
                'open'      => true,
                'points'    => $subpath,
            ), $this);
        }
    }
}
