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

    public function transform(callable $transformator): PathDataCommandInterface
    {
        if ($this->dx1 !== null) {
            list($this->dx1, $this->dy1) = $transformator([$this->dx1, $this->dy1]);
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
                $this->dx1 = $transformedX1;
                $this->dy1 = $transformedY1;
            }
        }

        list($this->dx, $this->dy) = $transformator([$this->dx, $this->dy]);

        return $this;
    }

    private function transformPointDiff(array $point): array
    {
        list($newX, $newY) = $transformator($point);

        return [
            $newX - $point[0],
            $newY - $point[1],
        ];
    }
}
