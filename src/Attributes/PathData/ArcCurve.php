<?php

namespace SVG\Attributes\PathData;

class ArcCurve extends AbstractPathDataCommand
{
    public function __construct(
        public float $rx,
        public float $ry,
        public float $angle,
        public bool $largeArcFlag,
        public bool $sweepFlag,
        public float $x,
        public float $y
    ) {}

    public static function getNames(): array
    {
        return ['A'];
    }

    public function getName(): string
    {
        return 'A';
    }

    public function __toString(): string
    {
        $largeArcFlag = $this->largeArcFlag ? 1 : 0;
        $sweepFlag = $this->sweepFlag ? 1 : 0;

        return "{$this->getName()} {$this->rx} {$this->ry} {$this->angle} {$largeArcFlag} {$sweepFlag} {$this->x} {$this->y}";
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
