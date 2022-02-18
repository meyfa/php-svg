<?php

namespace SVG;

use SVG\Rasterization\Renderers\MultiPassRenderer;

/**
 * @requires extension gd
 * @covers \SVG\Rasterization\Renderers\MultiPassRenderer
 *
 * @SuppressWarnings(PHPMD)
 */
class MultiPassRendererTest extends \PHPUnit\Framework\TestCase
{
    // helper function
    private function isGdImage()
    {
        if (class_exists('\GdImage', false)) {
            // PHP >=8: gd images are objects
            return $this->isInstanceOf('\GdImage');
        } else {
            // PHP <8: gd images are resources
            return $this->isType('resource');
        }
    }

    public function testRender()
    {
        $rast = new \SVG\Rasterization\SVGRasterizer(10, 20, null, 100, 200);
        $options = array(
            'option1' => 'option1-value',
            'option2' => 'option2-value',
        );
        $node = $this->getMockForAbstractClass('\SVG\Nodes\SVGNode');

        $params = array(
            'param1' => 'param1-value',
            'param2' => 'param2-value',
        );

        // should call prepareRenderParams with correct arguments
        $obj = $this->getMockForAbstractClass('\SVG\Rasterization\Renderers\MultiPassRenderer');
        $obj->expects($this->once())->method('prepareRenderParams')->with(
            $this->identicalTo($rast),
            $this->identicalTo($options)
        )->willReturn($params);
        $obj->render($rast, $options, $node);

        // should call renderFill with correct fill color
        $node->setStyle('fill', '#AAAAAA');
        $obj = $this->getMockForAbstractClass('\SVG\Rasterization\Renderers\MultiPassRenderer');
        $obj->method('prepareRenderParams')->willReturn($params);
        $obj->expects($this->once())->method('renderFill')->with(
            $this->isGdImage(),
            $this->identicalTo($params),
            $this->identicalTo(0xAAAAAA)
        );
        $obj->render($rast, $options, $node);

        // should not call renderFill with 'fill: none' style
        $node->setStyle('fill', 'none');
        $obj = $this->getMockForAbstractClass('\SVG\Rasterization\Renderers\MultiPassRenderer');
        $obj->method('prepareRenderParams')->willReturn($params);
        $obj->expects($this->never())->method('renderFill');
        $obj->render($rast, $options, $node);

        // should call renderStroke with correct stroke color and width
        $node->setStyle('stroke', '#BBBBBB');
        $node->setStyle('stroke-width', '2px');
        $obj = $this->getMockForAbstractClass('\SVG\Rasterization\Renderers\MultiPassRenderer');
        $obj->method('prepareRenderParams')->willReturn($params);
        $obj->expects($this->once())->method('renderStroke')->with(
            $this->isGdImage(),
            $this->identicalTo($params),
            $this->identicalTo(0xBBBBBB),
            $this->equalTo(20)
        );
        $obj->render($rast, $options, $node);

        // should not call renderStroke with 'stroke: none' style
        $node->setStyle('stroke', 'none');
        $obj = $this->getMockForAbstractClass('\SVG\Rasterization\Renderers\MultiPassRenderer');
        $obj->method('prepareRenderParams')->willReturn($params);
        $obj->expects($this->never())->method('renderStroke');
        $obj->render($rast, $options, $node);

        imagedestroy($rast->getImage());
    }
}
