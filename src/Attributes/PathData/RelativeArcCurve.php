<?php

namespace SVG\Attributes\PathData;

class RelativeArcCurve extends AbstractPathDataCommand
{
    public function __construct(
        public float $rx,
        public float $ry,
        public float $angle,
        public bool $largeArcFlag,
        public bool $sweepFlag,
        public float $dx,
        public float $dy
    ) {}

    public static function getNames(): array
    {
        return ['a'];
    }

    public function getName(): string
    {
        return 'a';
    }

    public function __toString(): string
    {
        $largeArcFlag = $this->largeArcFlag ? 1 : 0;
        $sweepFlag = $this->sweepFlag ? 1 : 0;

        return "{$this->getName()} {$this->rx} {$this->ry} {$this->angle} {$largeArcFlag} {$sweepFlag} {$this->dx} {$this->dy}";
    }

    public function getPoints(): array
    {
        list($prevX, $prevY) = $this->getPrevious()->getLastPoint();

        return [[$prevX + $this->dx, $prevY + $this->dy]];
    }

    public function getLastPoint(): array
    {
        return $this->getPoints()[0];
    }
}
