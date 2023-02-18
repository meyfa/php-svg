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
        $points = [];

        if ($this->x1 !== null) {
            $points[] = [$this->x1, $this->y1];
        }

        $points = [
            [$this->x2, $this->y2],
            [$this->x, $this->y],
        ];

        return $points;
    }

    public function getLastPoint(): array
    {
        return [$this->x, $this->y];
    }

    public function transform(callable $transformator): PathDataCommandInterface
    {
        if ($this->x1 !== null) {
            list($this->x1, $this->y1) = $transformator([$this->x1, $this->y1]);
        } else {
            $prevPoints = $this->getPrevious()->getPoints();
            $midPoint = array_pop($prevPoints);

            if ($this->getPrevious() instanceof BezierCurve) {
                $bezPoint = array_pop($prevPoints);
            } else {
                /**
                 * If the S command doesn't follow another S or C command, then
                 * the current position of the cursor is used as the first control point.
                 */
                $bezPoint = $midPoint;
            }

            $virtualX1 = $midPoint[0] + $midPoint[0] - $bezPoint[0];
            $virtualY1 = $midPoint[1] + $midPoint[1] - $bezPoint[1];

            list($transformedX1, $transformedY1) = $transformator([$this->virtualX1, $this->virtualY1], true);

            if ($transformedX1 !== $virtualX1 || $transformedY1 !== $virtualY1) {
                $this->x1 = $transformedX1;
                $this->y1 = $transformedY1;
            }
        }

        list($this->x2, $this->y2) = $transformator([$this->x2, $this->y2]);
        list($this->x, $this->y) = $transformator([$this->x, $this->y]);

        return $this;
    }
}
