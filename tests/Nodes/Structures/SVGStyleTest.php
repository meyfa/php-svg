<?php

namespace SVG;

use SVG\Nodes\Structures\SVGStyle;
use SVG\Rasterization\SVGRasterizer;

/**
 * @SuppressWarnings(PHPMD)
 */
class SVGStyleTest extends \PHPUnit\Framework\TestCase
{
    public function testSetType()
    {
        $svgStyle = new SVGStyle();

        $this->assertInstanceOf('SVG\Nodes\Structures\SVGStyle', $svgStyle->setType('type_attribute'));
    }

    public function testGetType()
    {
        $svgStyle = new SVGStyle();
        $type = 'type_attribute';
        $svgStyle->setType($type);

        $this->assertSame($type, $svgStyle->getType());
    }

    public function testSetCss()
    {
        $svgStyle = new SVGStyle();

        $this->assertInstanceOf('SVG\Nodes\Structures\SVGStyle', $svgStyle->setCss('svg {background-color: beige;}'));
    }

    public function testRasterize()
    {
        $svgStyle = new SVGStyle();

        $rast = $this->getMockBuilder('\SVG\Rasterization\SVGRasterizer')
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertNull($svgStyle->rasterize($rast));
    }
}
