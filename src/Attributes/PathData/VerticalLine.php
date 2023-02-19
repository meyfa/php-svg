<?php

namespace SVG\Attributes\PathData;

use SVG\Attributes\PathData\AbstractPathDataCommand;

class VerticalLine extends AbstractPathDataCommand
{
    public function __construct(
        public float $y
    ) {
    }

    public static function getNames(): array
    {
        return ['V'];
    }

    public function getName(): string
    {
        return 'V';
    }

    public function __toString(): string
    {
        return "{$this->getName()} {$this->y}";
    }

    public function getX(): int
    {
        return $this->getPrevious()->getLastPoint()[0];
    }

    public function getPoints(): array
    {
        return [[$this->getX(), $this->y]];
    }

    public function getLastPoint(): array
    {
        return $this->getPoints()[0];
    }

    public function transform(callable $transformator): PathDataCommandInterface
    {
        $x = $this->getX();
        list($newX, $this->y) = $transformator([$x, $this->y]);

        if ($newX !== $x) {
            return new Line($newX, $this->y);
        }

        return $this;
    }
}
