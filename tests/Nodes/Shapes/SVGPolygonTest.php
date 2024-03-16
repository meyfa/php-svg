<?php

namespace SVG\Tests\Nodes\Shapes;

use PHPUnit\Framework\TestCase;
use SVG\Nodes\Shapes\SVGPolygon;
use SVG\Rasterization\SVGRasterizer;

/**
 * @coversDefaultClass \SVG\Nodes\Shapes\SVGPolygon
 * @covers ::<!public>
 *
 * @SuppressWarnings(PHPMD)
 */
class SVGPolygonTest extends TestCase
{
    /**
     * @covers ::__construct
     */
    public function test__construct(): void
    {
        // should set empty points by default
        $obj = new SVGPolygon();
        $this->assertSame([], $obj->getPoints());

        // should set points when provided
        $points = [
            [42.5, 42.5],
            [37, 37],
        ];
        $obj = new SVGPolygon($points);
        $this->assertEquals($points, $obj->getPoints());
    }

    /**
     * @covers ::rasterize
     */
    public function testRasterize(): void
    {
        $points = [
            [42.5, 42.5],
            [37, 37],
        ];

        $obj = new SVGPolygon($points);

        $rast = $this->getMockBuilder(SVGRasterizer::class)
            ->disableOriginalConstructor()
            ->getMock();

        // should call image renderer with correct options
        $rast->expects($this->once())->method('render')->with(
            $this->identicalTo('polygon'),
            $this->equalTo([
                'open' => false,
                'points' => $points,
                'fill-rule' => 'nonzero',
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

    /**
     * @covers ::rasterize
     */
    public function testRasterizeShouldRespectFillRule(): void
    {
        $points = [
            [42.5, 42.5],
            [37, 37],
        ];

        $obj = new SVGPolygon($points);

        $attributeToExpectedFillRule = [
            '' => 'nonzero',
            " \n " => 'nonzero',
            'nonzero' => 'nonzero',
            '  nonzero  ' => 'nonzero',
            'nonZero' => 'nonzero',
            'evenodd' => 'evenodd',
            '  evenodd  ' => 'evenodd',
            ' evenOdd ' => 'evenodd',
            'foo' => 'foo',
        ];
        foreach ($attributeToExpectedFillRule as $attribute => $expectedFillRule) {
            $rasterizer = $this->getMockBuilder(SVGRasterizer::class)
                ->disableOriginalConstructor()
                ->getMock();

            $rasterizer->expects($this->once())->method('render')->with(
                $this->identicalTo('polygon'),
                $this->equalTo([
                    'open' => false,
                    'points' => $points,
                    'fill-rule' => $expectedFillRule,
                ]),
                $this->identicalTo($obj)
            );

            $obj->setStyle('fill-rule', $attribute);
            $obj->rasterize($rasterizer);
        }
    }
}
