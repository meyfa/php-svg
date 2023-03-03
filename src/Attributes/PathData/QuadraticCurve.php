<?php

namespace SVG\Attributes\PathData;

class QuadraticCurve extends AbstractPathDataCommand
{
    public ?float $x1;
    public ?float $y1;

    public float $x;
    public float $y;

    public function __construct(
        float $x1,
        float $y1,
        ?float $x2 = null,
        ?float $y2 = null,
    ) {
        if (($x2 === null || $y2 === null) && $x2 !== $y2) {
            throw new \InvalidArgumentException("Both \$x2 and \$y2 must be set or empty");
        }

        if ($x2 === null) {
            $x2 = $x1;
            $y2 = $y1;
            $x1 = null;
            $y1 = null;
        }

        $this->x1 = $x1;
        $this->y1 = $y1;
        $this->x = $x2;
        $this->y = $y2;
    }

    public static function getNames(): array
    {
        return ['Q', 'T'];
    }

    public function getName(): string
    {
        return $this->x1 === null ? 'T' : 'Q';
    }

    public function __toString(): string
    {
        $firstPoint = $this->x1 !== null ? "{$this->x1} {$this->y1} " : "";

        return "{$this->getName()} {$firstPoint}{$this->x} {$this->y}";
    }

    public function getPoints(): array
    {
        $points = [
            [$this->x, $this->y]
        ];

        if ($this->x1 !== null) {
            array_unshift($points, [$this->x1, $this->y1]);
        }

        return $points;
    }

    public function getLastPoint(): array
    {
        return [$this->x, $this->y];
    }
}
