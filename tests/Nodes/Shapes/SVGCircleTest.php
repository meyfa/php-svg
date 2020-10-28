<?php

namespace SVG;

use SVG\Nodes\Shapes\SVGCircle;

/**
 * @coversDefaultClass \SVG\Nodes\Shapes\SVGCircle
 * @covers ::<!public>
 *
 * @SuppressWarnings(PHPMD)
 */
class SVGCircleTest extends \PHPUnit\Framework\TestCase
{
  /**
     * @covers ::__construct
     */
    public function test__construct()
    {
        // should not set any attributes by default
        $obj = new SVGCircle();
        $this->assertSame(array(), $obj->getSerializableAttributes());

        // should set attributes when provided
        $obj = new SVGCircle(37, 42, 100);
        $this->assertSame(array(
            'cx' => '37',
            'cy' => '42',
            'r' => '100',
        ), $obj->getSerializableAttributes());
    }

    /**
     * @covers ::getCenterX
     */
    public function testGetCenterX()
    {
        $obj = new SVGCircle();

        // should return the attribute
        $obj->setAttribute('cx', 42);
        $this->assertSame('42', $obj->getCenterX());
    }

    /**
     * @covers ::setCenterX
     */
    public function testSetCenterX()
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
    public function testGetCenterY()
    {
        $obj = new SVGCircle();

        // should return the attribute
        $obj->setAttribute('cy', 42);
        $this->assertSame('42', $obj->getCenterY());
    }

    /**
     * @covers ::setCenterY
     */
    public function testSetCenterY()
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
    public function testGetRadius()
    {
        $obj = new SVGCircle();

        // should return the attribute
        $obj->setAttribute('r', 42);
        $this->assertSame('42', $obj->getRadius());
    }

    /**
     * @covers ::setRadius
     */
    public function testSetRadius()
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
    public function testRasterize()
    {
        $obj = new SVGCircle(37, 42, 100);

        $rast = $this->getMockBuilder('\SVG\Rasterization\SVGRasterizer')
            ->disableOriginalConstructor()
            ->getMock();

        // should call image renderer with correct options
        $rast->expects($this->once())->method('render')->with(
            $this->identicalTo('ellipse'),
            $this->identicalTo(array(
                'cx' => '37',
                'cy' => '42',
                'rx' => '100',
                'ry' => '100',
            )),
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
