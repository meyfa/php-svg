<?php

namespace SVG\Attributes;

interface SVGAttributeInterface
{
    public function getName(): string;

    /**
     * Returns a string representation of attribute value
     */
    public function __toString(): string;
}
