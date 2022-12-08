<?php

namespace SVG;

use SVG\Nodes\Shapes\SVGPath;

/**
 * @coversDefaultClass \SVG\Nodes\Shapes\SVGPath
 * @covers ::<!public>
 *
 * @SuppressWarnings(PHPMD)
 */
class SVGPathTest extends \PHPUnit\Framework\TestCase
{
    private static $sampleDescription = 'M100,100 h20 Z M200,200 h20';
    private static $sampleCommands = [
        ['id' => 'M', 'args' => [100.0, 100.0]],
        ['id' => 'h', 'args' => [20.0]],
        ['id' => 'Z', 'args' => []],
        ['id' => 'M', 'args' => [200.0, 200.0]],
        ['id' => 'h', 'args' => [20.0]],
    ];

    /**
     * @covers ::__construct
     */
    public function test__construct()
    {
        // should not set any attributes by default
        $obj = new SVGPath();
        $this->assertSame([], $obj->getSerializableAttributes());

        // should set attributes when provided
        $obj = new SVGPath(self::$sampleDescription);
        $this->assertSame([
            'd' => self::$sampleDescription,
        ], $obj->getSerializableAttributes());
    }

    /**
     * @covers ::getDescription
     */
    public function testGetDescription()
    {
        $obj = new SVGPath();

        // should return the attribute
        $obj->setAttribute('d', self::$sampleDescription);
        $this->assertSame(self::$sampleDescription, $obj->getDescription());
    }

    /**
     * @covers ::setDescription
     */
    public function testSetDescription()
    {
        $obj = new SVGPath();

        // should update the attribute
        $obj->setDescription(self::$sampleDescription);
        $this->assertSame(self::$sampleDescription, $obj->getAttribute('d'));

        // should return same instance
        $this->assertSame($obj, $obj->setDescription(self::$sampleDescription));
    }

    /**
     * @covers ::rasterize
     */
    public function testRasterizeWithNull()
    {
        $obj = new SVGPath();

        $rast = $this->getMockBuilder('\SVG\Rasterization\SVGRasterizer')
            ->disableOriginalConstructor()
            ->getMock();

        // should not manipulate anything
        $rast->expects($this->never())->method($this->anything());
        $obj->rasterize($rast);
    }

    /**
     * @covers ::rasterize
     */
    public function testRasterize()
    {
        $obj = new SVGPath(self::$sampleDescription);

        // setup mocks
        $rast = $this->getMockBuilder('\SVG\Rasterization\SVGRasterizer')
            ->disableOriginalConstructor()
            ->getMock();

        // should call image renderer with correct options
        $rast->expects($this->once())->method('render')->with(
            $this->identicalTo('path'),
            $this->identicalTo([
                'commands' => self::$sampleCommands,
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
    public function testRasterizeShouldRespectFillRule()
    {
        $obj = new SVGPath(self::$sampleDescription);

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
            $rasterizer = $this->getMockBuilder('\SVG\Rasterization\SVGRasterizer')
                ->disableOriginalConstructor()
                ->getMock();

            $rasterizer->expects($this->once())->method('render')->with(
                $this->identicalTo('path'),
                $this->equalTo([
                    'commands' => self::$sampleCommands,
                    'fill-rule' => $expectedFillRule,
                ]),
                $this->identicalTo($obj)
            );

            $obj->setStyle('fill-rule', $attribute);
            $obj->rasterize($rasterizer);
        }
    }
}
