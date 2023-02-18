<?php

namespace SVG\Attributes;

use SVG\Attributes\PathData\PathDataInstructionInterface;

class SVGPathData implements SVGAttributeInterface
{
    public const ATTRIBUTE_NAME = 'd';

    private ?PathDataInstructionInterface $head = null;

    public function getName(): string
    {
        return self::ATTRIBUTE_NAME;
    }

    public function __toString(): string
    {
    }

    public function addInstruction(PathDataInstructionInterface $instruction): self
    {
        if ($this->head) {
            $instruction->setPrevious($this->head);
        }
        $this->head = $instruction;

        return $this;
    }
}