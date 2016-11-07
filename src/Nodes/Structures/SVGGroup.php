<?php

namespace JangoBrick\SVG\Nodes\Structures;

use JangoBrick\SVG\Nodes\SVGNodeContainer;

/**
 * Represents the SVG tag 'g'.
 */
class SVGGroup extends SVGNodeContainer
{
    const TAG_NAME = 'g';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @inheritDoc
     * @SuppressWarnings("unused")
     */
    public static function constructFromAttributes($attrs)
    {
        return new self();
    }
}
