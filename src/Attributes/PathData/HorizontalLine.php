<?php

namespace SVG\Attributes\PathData;

use SVG\Attributes\PathData\AbstractPathDataCommand;

class HorizontalLine extends AbstractPathDataCommand
{
    public function __construct(
        public float $x
    ) {
    }

    public static function getNames(): array
    {
        return ['H'];
    }

    public function getName(): string
    {
        return 'H';
    }

    public function __toString(): string
    {
        return "{$this->getName()} {$this->x}";
    }

    public function getY(): int
    {
        return $this->getPrevious()->getLastPoint()[1];
    }

    public function getPoints(): array
    {
        return [[$this->x, $this->getY()]];
    }

    public function getLastPoint(): array
    {
        return $this->getPoints()[0];
    }

    public function transform(callable $transformator): PathDataCommandInterface
    {
        $y = $this->getY();
        list($this->x, $newY) = $transformator([$this->x, $y]);

        if ($newY !== $y) {
            return new Line($this->x, $newY);
        }

        return $this;
    }
}
