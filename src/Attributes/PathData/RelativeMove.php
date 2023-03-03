<?php

namespace SVG\Attributes\PathData;

class RelativeMove extends AbstractPathDataCommand
{
    public function __construct(
        public float $dx,
        public float $dy,
    ) {
    }

    public static function getNames(): array
    {
        return ['m'];
    }

    public function getName(): string
    {
        return 'm';
    }

    public function requiresPrevious(): bool
    {
        return true;
    }

    public function __toString(): string
    {
        return "{$this->getName()} {$this->dx} {$this->dy}";
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
