<?php

namespace SVG\Tests\Nodes\Shapes;

use PHPUnit\Framework\TestCase;
use SVG\Nodes\Shapes\SVGCircle;
use SVG\Rasterization\SVGRasterizer;

/**
 * @coversDefaultClass \SVG\Nodes\Shapes\SVGCircle
 * @covers ::<!public>
 *
 * @SuppressWarnings(PHPMD)
 */
class SVGCircleTest extends TestCase
{
    /**
     * @covers ::__construct
     */
    public function test__construct(): void
    {
        // should not set any attributes by default
        $obj = new SVGCircle();
        $this->assertSame([], $obj->getSerializableAttributes());

        // should set attributes when provided
        $obj = new SVGCircle(37, 42, 100);
        $this->assertSame([
            'cx' => '37',
            'cy' => '42',
            'r' => '100',
        ], $obj->getSerializableAttributes());
    }

    /**
     * @covers ::getCenterX
     */
    public function testGetCenterX(): void
    {
        $obj = new SVGCircle();

        // should return the attribute
        $obj->setAttribute('cx', 42);
        $this->assertSame('42', $obj->getCenterX());
    }

    /**
     * @covers ::setCenterX
     */
    public function testSetCenterX(): void
    {
        $obj = new SVGCircle();

        // should update the attribute
        $obj->setCenterX(42);
        $this->assertSame('42', $obj->getAttribute('cx'));

        // should return same instance
        $this->assertSame($obj, $obj->setCenterX(42));
    }

    /**
     * @covers ::getCenterY
     */
    public function testGetCenterY(): void
    {
        $obj = new SVGCircle();

        // should return the attribute
        $obj->setAttribute('cy', 42);
        $this->assertSame('42', $obj->getCenterY());
    }

    /**
     * @covers ::setCenterY
     */
    public function testSetCenterY(): void
    {
        $obj = new SVGCircle();

        // should update the attribute
        $obj->setCenterY(42);
        $this->assertSame('42', $obj->getAttribute('cy'));

        // should return same instance
        $this->assertSame($obj, $obj->setCenterY(42));
    }

    /**
     * @covers ::getRadius
     */
    public function testGetRadius(): void
    {
        $obj = new SVGCircle();

        // should return the attribute
        $obj->setAttribute('r', 42);
        $this->assertSame('42', $obj->getRadius());
    }

    /**
     * @covers ::setRadius
     */
    public function testSetRadius(): void
    {
        $obj = new SVGCircle();

        // should update the attribute
        $obj->setRadius(42);
        $this->assertSame('42', $obj->getAttribute('r'));

        // should return same instance
        $this->assertSame($obj, $obj->setRadius(42));
    }

    /**
     * @covers ::rasterize
     */
    public function testRasterize(): void
    {
        $obj = new SVGCircle(37, 42, 100);

        $rast = $this->getMockBuilder(SVGRasterizer::class)
            ->disableOriginalConstructor()
            ->getMock();

        // should call image renderer with correct options
        $rast->expects($this->once())->method('render')->with(
            $this->identicalTo('ellipse'),
            $this->identicalTo([
                'cx' => 37.0,
                'cy' => 42.0,
                'rx' => 100.0,
                'ry' => 100.0,
            ]),
            $this->identicalTo($obj)
        );
        $obj->rasterize($rast);

        // should not rasterize with 'display: none' style
        $obj->setStyle('display', 'none');
        $obj->rasterize($rast);

        // should not rasterize with 'visibility: hidden' or 'collapse' style
        $obj->setStyle('display', null);
        $obj->setStyle('visibility', 'hidden');
        $obj->rasterize($rast);
        $obj->setStyle('visibility', 'collapse');
        $obj->rasterize($rast);
    }
}
