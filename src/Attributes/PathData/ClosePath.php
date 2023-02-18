<?php

namespace SVG\Attributes\PathData;

class ClosePath extends AbstractPathDataInstruction
{
    protected bool $requiresPrevious = true;

    public static function getNames(): array
    {
        return ['z', 'Z'];
    }

    public function getName(): string
    {
        return 'z';
    }

    public function __toString(): string
    {
        return $this->getName();
    }

    public function getPoints(): array
    {
        return [];
    }

    public function getLastPoint(): array
    {
        return $this->getPrevious()->getLastPoint();
    }

    public function transform(callable $transformator): PathDataInstructionInterface
    {
        return $this;
    }
}
