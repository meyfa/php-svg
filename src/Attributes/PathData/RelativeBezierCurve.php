<?php

namespace SVG\Attributes\PathData;

/**
 * @see https://developer.mozilla.org/en-US/docs/Web/SVG/Tutorial/Paths#b%C3%A9zier_curves
 */
class RelativeBezierCurve extends AbstractPathDataCommand
{
    protected ?float $dx1;
    protected ?float $dy1;

    protected float $dx2;
    protected float $dy2;

    protected float $dx;
    protected float $dy;

    public function __construct(
        float $dx1,
        float $dy1,
        float $dx2,
        float $dy2,
        ?float $dx3 = null,
        ?float $dy3 = null,
    ) {
        if (($dx3 === null || $dy3 === null) && $dx3 !== $dy3) {
            throw new \InvalidArgumentException("Both \$dx3 and \$dy3 must be set or empty");
        }

        if ($dx3 === null) {
            $dx3 = $dx2;
            $dy3 = $dy2;
            $dx2 = $dx1;
            $dy2 = $dy1;
            $dx1 = null;
            $dy1 = null;
        }

        $this->dx1 = $dx1;
        $this->dy1 = $dy1;
        $this->dx2 = $dx2;
        $this->dy2 = $dy2;
        $this->dx = $dx3;
        $this->dy = $dy3;
    }

    public static function getNames(): array
    {
        return ['c', 's'];
    }

    public function getName(): string
    {
        return $this->dx1 === null
            ? 's'
            : 'c';
    }

    public function __toString(): string
    {
        $lastPoint = $this->dx1 === null
            ? ""
            : " {$this->dx1} {$this->dy1}";

        return "{$this->getName()}{$firstPoint} {$this->dx2} {$this->dy2} {$this->dx} {$this->dy}";
    }

    public function getPoints(): array
    {
        list($x, $y) = $this->getPrevious()->getLastPoint();

        $points = [];

        if ($this->dx1 !== null) {
            $points[] = [$x + $this->dx1, $y + $this->dy1];
        }

        $points = [
            [$x + $this->dx2, $y + $this->dy2],
            [$x + $this->dx, $y + $this->dy],
        ];

        return $points;
    }

    public function getLastPoint(): array
    {
        list($x, $y) = $this->getPrevious()->getLastPoint();

        return [$x + $this->dx, $y + $this->dy];
    }

    public function transform(callable $transformator): PathDataCommandInterface
    {
        list($x, $y) = $this->getPrevious()->getLastPoint();

        if ($this->dx1 !== null) {
            list($changeX1, $changeY1) = $this->transformPointDiff([$x + $this->dx1, $y + $this->dy1]);
            $this->dx1 += $changeX1;
            $this->dy1 += $changeY1;
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
                $this->dx1 = $transformedX1;
                $this->dy1 = $transformedY1;
            }
        }

        list($changeX2, $changeY2) = $this->transformPointDiff([$x + $this->dx2, $y + $this->dy2]);
        $this->dx2 += $changeX2;
        $this->dy2 += $changeY2;

        list($changeX, $changeY) = $this->transformPointDiff([$x + $this->dx, $y + $this->dy]);
        $this->dx += $changeX;
        $this->dy += $changeY;

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
