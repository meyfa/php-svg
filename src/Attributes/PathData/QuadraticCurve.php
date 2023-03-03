<?php

namespace SVG\Attributes\PathData;

class QuadraticCurve extends AbstractPathDataCommand
{
    public float $x1;
    public float $y1;

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
            $this->x = $x1;
            $this->y = $y1;

            return;
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
        $firstPoint = $this->x1 !== null ? "{$this->x1} ${$this->x2} " : "";

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

    public function transform(callable $transformator): PathDataCommandInterface
    {
        if ($this->x1 !== null) {
            list($this->x1, $this->y1) = $transformator([$this->x1, $this->y1]);
        } else {
            $prevPoints = $this->getPrevious()->getPoints();
            $midPoint = array_pop($prevPoints);

            if ($this->getPrevious() instanceof QuadraticCurve) {
                $bezPoint = array_pop($prevPoints);
            } else {
                /**
                 * If the T command doesn't follow another T or Q command, then
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

        list($this->x, $this->y) = $transformator([$this->x, $this->y]);

        return $this;
    }
}
