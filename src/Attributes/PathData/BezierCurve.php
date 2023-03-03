<?php

namespace SVG\Attributes\PathData;

/**
 * @see https://developer.mozilla.org/en-US/docs/Web/SVG/Tutorial/Paths#b%C3%A9zier_curves
 */
class BezierCurve extends AbstractPathDataCommand
{
    protected ?float $x1;
    protected ?float $y1;

    protected float $x2;
    protected float $y2;

    protected float $x;
    protected float $y;

    public function __construct(
        float $x1,
        float $y1,
        float $x2,
        float $y2,
        ?float $x3 = null,
        ?float $y3 = null,
    ) {
        if (($x3 === null || $y3 === null) && $x3 !== $y3) {
            throw new \InvalidArgumentException("Both \$x3 and \$y3 must be set or empty");
        }

        if ($x3 === null) {
            $x3 = $x2;
            $y3 = $y2;
            $x2 = $x1;
            $y2 = $y1;
            $x1 = null;
            $y1 = null;
        }

        $this->x1 = $x1;
        $this->y1 = $y1;
        $this->x2 = $x2;
        $this->y2 = $y2;
        $this->x = $x3;
        $this->y = $y3;
    }

    public static function getNames(): array
    {
        return ['C', 'S'];
    }

    public function getName(): string
    {
        return $this->x1 === null
            ? 'S'
            : 'C';
    }

    public function __toString(): string
    {
        $lastPoint = $this->x1 === null
            ? ""
            : " {$this->x1} {$this->y1}";

        return "{$this->getName()}{$firstPoint} {$this->x2} {$this->y2} {$this->x} {$this->y}";
    }

    public function getPoints(): array
    {
        $points = [
            [$this->x2, $this->y2],
            [$this->x, $this->y],
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
