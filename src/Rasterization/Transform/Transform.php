<?php

namespace SVG\Rasterization\Transform;

/**
 * This class stores a transformation matrix for affine 2D transforms.
 * It is mutable, meaning to create a transform that translates, then scales, then rotates, the following can be used:
 *
 * <code>
 * $matrix = Transform::identity();
 * $matrix->translate(20, 30);
 * $matrix->scale(1.5, 1.3);
 * $matrix->rotate(deg2rad(45));
 * </code>
 *
 * Points can be mapped from inside the transformed space back into non-transformed space
 * (to see where the points of a shape that is affected by the transform will end up on the rasterized image).
 * This is done by passing the coordinates as references, to avoid having to deal with constructing arrays all the time.
 */
class Transform
{
    private $matrix;

    /**
     * Create a transform from the given matrix. The entries [a, b, c, d, e, f] represent the following matrix:
     *
     * <code>
     * |a  c  e|
     * |b  d  f|
     * |0  0  1|
     * </code>
     *
     * Note that the last row is constant and need not (or rather, must not) be passed.
     *
     * @param array $matrix The transformation matrix [a, b, c, d, e, f].
     */
    public function __construct(array $matrix)
    {
        $this->matrix = $matrix;
    }

    /**
     * Obtain an identity transform.
     *
     * @return Transform The new transform.
     */
    public static function identity(): Transform
    {
        return new self([1, 0, 0, 1, 0, 0]);
    }

    // computation functions

    /**
     * Map the given coordinates from transformed space into regular space, for example to see where the points of a
     * shape affected by this transform will end up on the rasterized image.
     *
     * @param float $x A reference to the x coordinate. This will be updated with the transform result.
     * @param float $y A reference to the y coordinate. This will be updated with the transform result.
     * @return void
     */
    public function map(float &$x, float &$y): void
    {
        $mappedX = $this->matrix[0] * $x + $this->matrix[2] * $y + $this->matrix[4];
        $mappedY = $this->matrix[1] * $x + $this->matrix[3] * $y + $this->matrix[5];
        $x = $mappedX;
        $y = $mappedY;
    }

    /**
     * Given a set of coordinates, this determines the mapped coordinates and appends them consecutively to the
     * destination array.
     *
     * @param float $x The x coordinate.
     * @param float $y The y coordinate.
     * @param array $destination A reference to the destination array, into which two new entries will be appended.
     * @return void
     */
    public function mapInto(float $x, float $y, array &$destination): void
    {
        $this->map($x, $y);
        $destination[] = $x;
        $destination[] = $y;
    }

    /**
     * This computes the side lengths that a rectangle with the given size would end up having,
     * after applying the transform. Note that this doesn't measure the bounding box but the actual width and height.
     *
     * @param float $width  The original width.
     * @param float $height The original height.
     * @return void
     */
    public function resize(float &$width, float &$height): void
    {
        $width  *= hypot($this->matrix[0], $this->matrix[1]);
        $height *= hypot($this->matrix[2], $this->matrix[3]);
    }

    // mutation functions

    /**
     * Post-multiply this transform by the given transform. The result will be stored on this object.
     * For example, if the given transform represents a translation, calling
     *
     * <code>$this->multiply($translation);</code>
     *
     * is the same as calling
     *
     * <code>$this->translate($dx, $dy);</code>
     *
     * In other words: Let M be this transform's homogenous matrix, and T be the other transform's homogenous matrix.
     * Then perform the assignment
     *
     * <code>M := M x T</code>
     *
     * @param Transform $other The transform to multiply this one with.
     * @return void
     */
    public function multiply(Transform $other): void
    {
        $this->matrix = [
            $other->matrix[0] * $this->matrix[0] + $other->matrix[1] * $this->matrix[2],
            $other->matrix[0] * $this->matrix[1] + $other->matrix[1] * $this->matrix[3],
            $other->matrix[2] * $this->matrix[0] + $other->matrix[3] * $this->matrix[2],
            $other->matrix[2] * $this->matrix[1] + $other->matrix[3] * $this->matrix[3],
            $other->matrix[4] * $this->matrix[0] + $other->matrix[5] * $this->matrix[2] + $this->matrix[4],
            $other->matrix[4] * $this->matrix[1] + $other->matrix[5] * $this->matrix[3] + $this->matrix[5],
        ];
    }

    /**
     * Apply a translation to this transform. This object will be mutated as a result of this operation.
     * This is the same as post-multiplying this transform with another transform representing a pure translation.
     *
     * @param float $dx The horizontal translation distance in terms of the current transform space.
     * @param float $dy The vertical translation distance in terms of the current transform space.
     * @return void
     */
    public function translate(float $dx, float $dy): void
    {
        /*
         * |a c e|     |1  0  dx|     |a  c  a*dx+c*dy+e|
         * |b d f|  x  |0  1  dy|  =  |b  d  b*dx+d*dy+f|
         * |0 0 1|     |0  0  1 |     |0  0  1          |
         */

        $this->matrix[4] += $dx * $this->matrix[0] + $dy * $this->matrix[2];
        $this->matrix[5] += $dx * $this->matrix[1] + $dy * $this->matrix[3];
    }

    /**
     * Apply a scale to this transform. This object will be mutated as a result of this operation.
     * This is the same as post-multiplying this transform with another transform representing a pure scale.
     *
     * @param float $sx The horizontal scaling factor in terms of the current transform space.
     * @param float $sy The vertical scaling factor in terms of the current transform space.
     * @return void
     */
    public function scale(float $sx, float $sy): void
    {
        /*
         * |a c e|     |sx  0   0|     |a*sx  c*sy  e|
         * |b d f|  x  |0   sy  0|  =  |b*sx  d*sy  f|
         * |0 0 1|     |0   0   1|     |0     0     1|
         */

        $this->matrix[0] *= $sx;
        $this->matrix[1] *= $sx;
        $this->matrix[2] *= $sy;
        $this->matrix[3] *= $sy;
    }

    /**
     * Apply a rotation to this transform. This object will be mutated as a result of this operation.
     * This is the same as post-multiplying this transform with another transform representing a pure rotation.
     *
     * @param float $radians The rotation angle (positive values representing clockwise rotations).
     * @return void
     */
    public function rotate(float $radians): void
    {
        /*
         * |a c e|     |cos(r)  -sin(t)  0|     |a*cos(t)+c*sin(t)  c*cos(t)-a*sin(t)  e|
         * |b d f|  x  |sin(t)   cos(t)  0|  =  |b*cos(t)+d*sin(t)  d*cos(t)-b*sin(t)  f|
         * |0 0 1|     |0        0       1|     |0                  0                  1|
         */

        $sin = sin($radians);
        $cos = cos($radians);

        // compute new entries
        $a = $this->matrix[0] * $cos + $this->matrix[2] * $sin;
        $b = $this->matrix[1] * $cos + $this->matrix[3] * $sin;
        $c = $this->matrix[2] * $cos - $this->matrix[0] * $sin;
        $d = $this->matrix[3] * $cos - $this->matrix[1] * $sin;

        // now we don't need the original entries, we can assign
        $this->matrix[0] = $a;
        $this->matrix[1] = $b;
        $this->matrix[2] = $c;
        $this->matrix[3] = $d;
    }

    /**
     * Apply a horizontal skew to this transform. This object will be mutated as a result of this operation.
     * This is the same as post-multiplying this transform with another transform representing a pure horizontal skew.
     *
     * @param float $radians The skew angle.
     * @return void
     */
    public function skewX(float $radians): void
    {
        /*
         * |a c e|     |1  tan(t)  0|     |a  a*tan(t)+c  e|
         * |b d f|  x  |0  1       0|  =  |b  b*tan(t)+d  f|
         * |0 0 1|     |0  0       1|     |0     0        1|
         */

        $tan = tan($radians);

        $this->matrix[2] += $this->matrix[0] * $tan;
        $this->matrix[3] += $this->matrix[1] * $tan;
    }

    /**
     * Apply a vertical skew to this transform. This object will be mutated as a result of this operation.
     * This is the same as post-multiplying this transform with another transform representing a pure vertical skew.
     *
     * @param float $radians The skew angle.
     * @return void
     */
    public function skewY(float $radians): void
    {
        /*
         * |a c e|     |1       0  0|     |a+c*tan(t)  c  e|
         * |b d f|  x  |tan(t)  1  0|  =  |b+d*tan(t)  d  f|
         * |0 0 1|     |0       0  1|     |0           0  1|
         */

        $tan = tan($radians);

        $this->matrix[0] += $this->matrix[2] * $tan;
        $this->matrix[1] += $this->matrix[3] * $tan;
    }
}
