<?php

namespace SVG\Attributes\PathData;

use SVG\Attributes\PathData\AbstractPathDataCommand;

class RelativeVerticalLine extends AbstractPathDataCommand
{
    public function __construct(
        public float $dy
    ) {
    }

    public static function getNames(): array
    {
        return ['v'];
    }

    public function getName(): string
    {
        return 'v';
    }

    public function __toString(): string
    {
        return "{$this->getName()} {$this->dy}";
    }

    public function getY(): int
    {
        return $this->getPrevious()->getLastPoint()[1] + $dy;
    }

    public function getX(): int
    {
        return $this->getPrevious()->getLastPoint()[0];
    }

    public function getPoints(): array
    {
        return [[$this->getX(), $this->getY()]];
    }

    public function getLastPoint(): array
    {
        return $this->getPoints()[0];
    }
}
