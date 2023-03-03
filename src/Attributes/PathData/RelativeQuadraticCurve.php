<?php

namespace SVG\Attributes\PathData;

class RelativeQuadraticCurve extends AbstractPathDataCommand
{
    public float $dx1;
    public float $dy1;

    public float $dx;
    public float $dy;

    public function __construct(
        float $dx1,
        float $dy1,
        ?float $dx2 = null,
        ?float $dy2 = null,
    ) {
        if (($dx2 === null || $dy2 === null) && $dx2 !== $dy2) {
            throw new \InvalidArgumentException("Both \$dx2 and \$dy2 must be set or empty");
        }

        if ($dx2 === null) {
            $this->dx = $dx1;
            $this->dy = $dy1;

            return;
        }

        $this->dx1 = $dx1;
        $this->dy1 = $dy1;
        $this->dx = $dx2;
        $this->dy = $dy2;
    }

    public static function getNames(): array
    {
        return ['q', 't'];
    }

    public function getName(): string
    {
        return $this->dx1 === null ? 't' : 'q';
    }

    public function __toString(): string
    {
        $firstPoint = $this->dx1 !== null ? "{$this->dx1} ${$this->dx2} " : "";

        return "{$this->getName()} {$firstPoint}{$this->dx} {$this->dy}";
    }

    public function getPoints(): array
    {
        list($x, $y) = $this->getPrevious()->getLastPoint();

        $points = [
            [$x + $this->dx, $y + $this->dy],
        ];

        if ($this->dx1 !== null) {
            array_unshift($points, [$x + $this->dx1, $y + $this->dy1]);
        }

        return $points;
    }

    public function getLastPoint(): array
    {
        list($x, $y) = $this->getPrevious()->getLastPoint();

        return [$x + $this->dx, $y + $this->dy];
    }
}
