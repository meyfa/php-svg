<?php

namespace SVG\Attributes\PathData;

class Move extends AbstractPathDataCommand
{
    public function __construct(
        public float $x,
        public float $y,
    ) {
    }

    public static function getNames(): array
    {
        return ['M'];
    }

    public function getName(): string
    {
        return 'M';
    }

    public function __toString(): string
    {
        return "{$this->getName()} {$this->x} {$this->y}";
    }

    public function getPoints(): array
    {
        return [[$this->x, $this->y]];
    }

    public function getLastPoint(): array
    {
        return $this->getPoints()[0];
    }

    public function transform(callable $transformator): PathDataCommandInterface
    {
        list($this->x, $this->y) = $transformator([$this->x, $this->y]);

        return $this;
    }
}
