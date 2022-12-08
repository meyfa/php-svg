<?php

namespace SVG;

use SVG\Rasterization\Transform\Transform;

/**
 * @coversDefaultClass \SVG\Rasterization\Transform\Transform
 *
 * @SuppressWarnings(PHPMD)
 */
class TransformTest extends \PHPUnit\Framework\TestCase
{
    private function assertMap(Transform $t, array $expected, array $source)
    {
        $t->map($source[0], $source[1]);
        $this->assertEqualsWithDelta($expected[0], $source[0], 10e-12);
        $this->assertEqualsWithDelta($expected[1], $source[1], 10e-12);
    }

    private function assertResized(Transform $t, array $expected, array $source)
    {
        $t->resize($source[0], $source[1]);
        $this->assertEqualsWithDelta($expected[0], $source[0], 10e-12);
        $this->assertEqualsWithDelta($expected[1], $source[1], 10e-12);
    }

    public function testIdentity()
    {
        $t = Transform::identity();
        $this->assertMap($t, [0, 0], [0, 0]);
        $this->assertMap($t, [123, 456], [123, 456]);
        $this->assertMap($t, [-123, -456], [-123, -456]);
    }

    /**
     * @covers ::resize
     */
    public function testResize()
    {
        $t = Transform::identity();
        $this->assertResized($t, [123, 456], [123, 456]);

        // translation is irrelevant
        $t = Transform::identity();
        $t->translate(500, 1000);
        $this->assertResized($t, [123, 456], [123, 456]);

        // should scale
        $t = Transform::identity();
        $t->scale(3, 5);
        $this->assertResized($t, [123 * 3, 456 * 5], [123, 456]);

        // lengths should not be affected by mirroring
        $t = Transform::identity();
        $t->scale(-3, -5);
        $this->assertResized($t, [123 * 3, 456 * 5], [123, 456]);

        // rotation is irrelevant
        $t = Transform::identity();
        $t->rotate(M_PI_4);
        $this->assertResized($t, [123, 456], [123, 456]);

        // skewX affects vertical side length
        $t = Transform::identity();
        $t->skewX(M_PI_4);
        $this->assertResized($t, [123, 456 * M_SQRT2], [123, 456]);
        $t->skewX(-M_PI_4);
        $t->skewX(-M_PI_4);
        $this->assertResized($t, [123, 456 * M_SQRT2], [123, 456]);

        // skewY affects horizontal side length
        $t = Transform::identity();
        $t->skewY(M_PI_4);
        $this->assertResized($t, [123 * M_SQRT2, 456], [123, 456]);
        $t->skewY(-M_PI_4);
        $t->skewY(-M_PI_4);
        $this->assertResized($t, [123 * M_SQRT2, 456], [123, 456]);

        // complex example
        $t = Transform::identity();
        $t->translate(100, 200);
        $t->rotate(M_PI_4);
        $t->skewX(M_PI_4);
        $t->scale(3, 5);
        $t->translate(100, 200);
        $this->assertResized($t, [123 * 3, 456 * 5 * M_SQRT2], [123, 456]);
    }

    public function testMultiply()
    {
        $t = Transform::identity();

        $t->multiply(Transform::identity());
        $this->assertMap($t, [123, 456], [123, 456]);

        // apply a translation
        $t->multiply(new Transform([1, 0, 0, 1, 123000, 456000]));
        $this->assertMap($t, [123123, 456456], [123, 456]);

        // apply another translation
        $t->multiply(new Transform([1, 0, 0, 1, -123, -456]));
        $this->assertMap($t, [123000, 456000], [123, 456]);

        // apply a more complex matrix (this has been computed with WolframAlpha)
        $t = Transform::identity();
        $t->multiply(new Transform([3, -7, -5, 9, 500, -250]));
        $this->assertMap($t, [-1411, 2993], [123, 456]);
    }

    public function testTranslate()
    {
        $t = Transform::identity();

        $t->translate(0, 0);
        $this->assertMap($t, [123, 456], [123, 456]);

        $t->translate(1000, 0);
        $this->assertMap($t, [1123, 456], [123, 456]);

        $t->translate(0, 2000);
        $this->assertMap($t, [1123, 2456], [123, 456]);

        $t->translate(-500, -2500);
        $this->assertMap($t, [623, 456 - 500], [123, 456]);

        // ensure that the formula is applied correctly (this has been computed with WolframAlpha)
        $t = new Transform([2, 3, 5, 7, 11, 13]);
        $t->translate(37, 41);
        $this->assertMap($t, [713, 1015], [59, 61]);
    }

    public function testScale()
    {
        $t = Transform::identity();

        $t->scale(1, 1);
        $this->assertMap($t, [12, 34], [12, 34]);

        $t->scale(-1, -1);
        $this->assertMap($t, [-12, -34], [12, 34]);

        $t->scale(-0.5, -0.5);
        $this->assertMap($t, [6, 17], [12, 34]);

        $t->scale(0, 0);
        $t->scale(1, 1);
        $this->assertMap($t, [0, 0], [12, 34]);

        // ensure that the formula is applied correctly (this has been computed with WolframAlpha)
        $t = new Transform([2, 3, 5, 7, 11, 13]);
        $t->scale(37, 41);
        $this->assertMap($t, [16882, 24069], [59, 61]);
    }

    public function testRotate()
    {
        $t = Transform::identity();

        $t->rotate(0);
        $this->assertMap($t, [123, 456], [123, 456]);

        $t->rotate(M_PI_2);
        $this->assertMap($t, [-456, 123], [123, 456]);

        $t->rotate(M_PI_2);
        $this->assertMap($t, [-123, -456], [123, 456]);

        // ensure that the formula is applied correctly (this has been computed with WolframAlpha)
        $t = new Transform([2, 3, 5, 7, 11, 13]);
        $t->rotate(M_PI_4);
        $this->assertMap($t, [11 + 298 * M_SQRT2, 13 + 417 * M_SQRT2], [59, 61]);
    }

    public function testSkewX()
    {
        $t = Transform::identity();

        $t->skewX(0);
        $this->assertMap($t, [123, 456], [123, 456]);

        $t->skewX(M_PI_4);
        $this->assertMap($t, [0, 0], [0, 0]);
        $this->assertMap($t, [123 + 456, 456], [123, 456]);

        $t->skewX(M_PI_4);
        $this->assertMap($t, [123 + 456 + 456, 456], [123, 456]);

        // ensure that the formula is applied correctly (this has been computed with WolframAlpha)
        $t = new Transform([2, 3, 5, 7, 11, 13]);
        $t->skewX(M_PI / 8);
        $this->assertMap($t, [312 + 122 * M_SQRT2, 434 + 183 * M_SQRT2], [59, 61]);
    }

    public function testSkewY()
    {
        $t = Transform::identity();

        $t->skewY(0);
        $this->assertMap($t, [123, 456], [123, 456]);

        $t->skewY(M_PI_4);
        $this->assertMap($t, [0, 0], [0, 0]);
        $this->assertMap($t, [123, 456 + 123], [123, 456]);

        $t->skewY(M_PI_4);
        $this->assertMap($t, [123, 456 + 123 + 123], [123, 456]);

        // ensure that the formula is applied correctly (this has been computed with WolframAlpha)
        $t = new Transform([2, 3, 5, 7, 11, 13]);
        $t->skewY(M_PI / 8);
        $this->assertMap($t, [139 + 295 * M_SQRT2, 204 + 413 * M_SQRT2], [59, 61]);
    }
}
