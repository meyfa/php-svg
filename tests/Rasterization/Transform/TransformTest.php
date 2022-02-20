<?php

namespace SVG;

use SVG\Rasterization\Transform\Transform;

/**
 * @covers \SVG\Rasterization\Transform\Transform
 *
 * @SuppressWarnings(PHPMD)
 */
class TransformTest extends \PHPUnit\Framework\TestCase
{
    private function assertMap(Transform $t, array $expected, array $source)
    {
        $x = $source[0];
        $y = $source[1];
        $t->map($x, $y);
        $this->assertEquals($expected, array($x, $y));
    }

    public function testIdentity()
    {
        $t = Transform::identity();
        $this->assertMap($t, array(0, 0), array(0, 0));
        $this->assertMap($t, array(123, 456), array(123, 456));
        $this->assertMap($t, array(-123, -456), array(-123, -456));
    }

    public function testMultiply()
    {
        $t = Transform::identity();

        $t->multiply(Transform::identity());
        $this->assertMap($t, array(123, 456), array(123, 456));

        // apply a translation
        $t->multiply(new Transform(array(1, 0, 0, 1, 123000, 456000)));
        $this->assertMap($t, array(123123, 456456), array(123, 456));

        // apply another translation
        $t->multiply(new Transform(array(1, 0, 0, 1, -123, -456)));
        $this->assertMap($t, array(123000, 456000), array(123, 456));

        // apply a more complex matrix (this has been computed with WolframAlpha)
        $t = Transform::identity();
        $t->multiply(new Transform(array(3, -7, -5, 9, 500, -250)));
        $this->assertMap($t, array(-1411, 2993), array(123, 456));
    }

    public function testTranslate()
    {
        $t = Transform::identity();

        $t->translate(0, 0);
        $this->assertMap($t, array(123, 456), array(123, 456));

        $t->translate(1000, 0);
        $this->assertMap($t, array(1123, 456), array(123, 456));

        $t->translate(0, 2000);
        $this->assertMap($t, array(1123, 2456), array(123, 456));

        $t->translate(-500, -2500);
        $this->assertMap($t, array(623, 456 - 500), array(123, 456));
    }

    public function testScale()
    {
        $t = Transform::identity();

        $t->scale(1, 1);
        $this->assertMap($t, array(12, 34), array(12, 34));

        $t->scale(-1, -1);
        $this->assertMap($t, array(-12, -34), array(12, 34));

        $t->scale(-0.5, -0.5);
        $this->assertMap($t, array(6, 17), array(12, 34));

        $t->scale(0, 0);
        $t->scale(1, 1);
        $this->assertMap($t, array(0, 0), array(12, 34));
    }

    public function testRotate()
    {
        $t = Transform::identity();

        $t->rotate(0);
        $this->assertMap($t, array(123, 456), array(123, 456));

        $t->rotate(pi() / 2);
        $this->assertMap($t, array(-456, 123), array(123, 456));

        $t->rotate(pi() / 2);
        $this->assertMap($t, array(-123, -456), array(123, 456));
    }

    public function testSkewX()
    {
        $t = Transform::identity();

        $t->skewX(0);
        $this->assertMap($t, array(123, 456), array(123, 456));

        $t->skewX(pi() / 4);
        $this->assertMap($t, array(0, 0), array(0, 0));
        $this->assertMap($t, array(123 + 456, 456), array(123, 456));

        $t->skewX(pi() / 4);
        $this->assertMap($t, array(123 + 456 + 456, 456), array(123, 456));
    }

    public function testSkewY()
    {
        $t = Transform::identity();

        $t->skewY(0);
        $this->assertMap($t, array(123, 456), array(123, 456));

        $t->skewY(pi() / 4);
        $this->assertMap($t, array(0, 0), array(0, 0));
        $this->assertMap($t, array(123, 456 + 123), array(123, 456));

        $t->skewY(pi() / 4);
        $this->assertMap($t, array(123, 456 + 123 + 123), array(123, 456));
    }
}
