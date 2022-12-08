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
    private static $sampleOptions = [
        'option1' => 'option1-value',
        'option2' => 'option2-value',
    ];
    private static $sampleParams = [
        'param1' => 'param1-value',
        'param2' => 'param2-value',
    ];

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

    public function testRenderShouldCallPrepare()
    {
        $rasterizer = new \SVG\Rasterization\SVGRasterizer(10, 20, null, 100, 200);
        $node = $this->getMockForAbstractClass('\SVG\Nodes\SVGNode');

        // should call prepareRenderParams with correct arguments
        $obj = $this->getMockForAbstractClass('\SVG\Rasterization\Renderers\MultiPassRenderer');
        $obj->expects($this->once())->method('prepareRenderParams')->with(
            $this->identicalTo(self::$sampleOptions),
            $this->isInstanceOf('\SVG\Rasterization\Transform\Transform')
        )->willReturn(self::$sampleParams);
        $obj->render($rasterizer, self::$sampleOptions, $node);

        imagedestroy($rasterizer->getImage());
    }

    public function testRenderShouldCallRenderFill()
    {
        $rasterizer = new \SVG\Rasterization\SVGRasterizer(10, 20, null, 100, 200);
        $node = $this->getMockForAbstractClass('\SVG\Nodes\SVGNode');

        // should call renderFill with correct fill color
        $node->setStyle('fill', '#AAAAAA');
        $obj = $this->getMockForAbstractClass('\SVG\Rasterization\Renderers\MultiPassRenderer');
        $obj->method('prepareRenderParams')->willReturn(self::$sampleParams);
        $obj->expects($this->once())->method('renderFill')->with(
            $this->isGdImage(),
            $this->identicalTo(self::$sampleParams),
            $this->identicalTo(0xAAAAAA)
        );
        $obj->render($rasterizer, self::$sampleOptions, $node);

        // should not call renderFill with 'fill: none' style
        $node->setStyle('fill', 'none');
        $obj = $this->getMockForAbstractClass('\SVG\Rasterization\Renderers\MultiPassRenderer');
        $obj->method('prepareRenderParams')->willReturn(self::$sampleParams);
        $obj->expects($this->never())->method('renderFill');
        $obj->render($rasterizer, self::$sampleOptions, $node);

        imagedestroy($rasterizer->getImage());
    }

    public function testRenderShouldCallRenderStroke()
    {
        $rasterizer = new \SVG\Rasterization\SVGRasterizer(10, 20, null, 100, 200);
        $node = $this->getMockForAbstractClass('\SVG\Nodes\SVGNode');

        // should call renderStroke with correct stroke color and width
        $node->setStyle('stroke', '#BBBBBB');
        $node->setStyle('stroke-width', '2px');
        $obj = $this->getMockForAbstractClass('\SVG\Rasterization\Renderers\MultiPassRenderer');
        $obj->method('prepareRenderParams')->willReturn(self::$sampleParams);
        $obj->expects($this->once())->method('renderStroke')->with(
            $this->isGdImage(),
            $this->identicalTo(self::$sampleParams),
            $this->identicalTo(0xBBBBBB),
            $this->equalTo(20)
        );
        $obj->render($rasterizer, self::$sampleOptions, $node);

        // should not call renderStroke with 'stroke: none' style
        $node->setStyle('stroke', 'none');
        $obj = $this->getMockForAbstractClass('\SVG\Rasterization\Renderers\MultiPassRenderer');
        $obj->method('prepareRenderParams')->willReturn(self::$sampleParams);
        $obj->expects($this->never())->method('renderStroke');
        $obj->render($rasterizer, self::$sampleOptions, $node);

        imagedestroy($rasterizer->getImage());
    }

    public function testRenderShouldRespectFillOpacity()
    {
        $rasterizer = new \SVG\Rasterization\SVGRasterizer(10, 20, null, 100, 200);
        $node = $this->getMockForAbstractClass('\SVG\Nodes\SVGNode');

        // should use fill-opacity for the alpha value (try with a few different notations)
        foreach (['0.5', '.5', '50%', '  0.5  ', '  50%  '] as $fillOpacity) {
            $node->setStyle('fill', '#AAAAAA');
            $node->setStyle('fill-opacity', $fillOpacity);
            $obj = $this->getMockForAbstractClass('\SVG\Rasterization\Renderers\MultiPassRenderer');
            $obj->method('prepareRenderParams')->willReturn(self::$sampleParams);
            $obj->expects($this->once())->method('renderFill')->with(
                $this->isGdImage(),
                $this->identicalTo(self::$sampleParams),
                $this->identicalTo(0x3FAAAAAA)
            );
            $obj->render($rasterizer, self::$sampleOptions, $node);
        }

        imagedestroy($rasterizer->getImage());
    }

    public function testRenderShouldRespectStrokeOpacity()
    {
        $rasterizer = new \SVG\Rasterization\SVGRasterizer(10, 20, null, 100, 200);
        $node = $this->getMockForAbstractClass('\SVG\Nodes\SVGNode');

        // should use stroke-opacity for the alpha value (try with a few different notations)
        foreach (['0.5', '.5', '50%', '  0.5  ', '  50%  '] as $strokeOpacity) {
            $node->setStyle('stroke', '#BBBBBB');
            $node->setStyle('stroke-opacity', $strokeOpacity);
            $node->setStyle('stroke-width', '2px');
            $obj = $this->getMockForAbstractClass('\SVG\Rasterization\Renderers\MultiPassRenderer');
            $obj->method('prepareRenderParams')->willReturn(self::$sampleParams);
            $obj->expects($this->once())->method('renderStroke')->with(
                $this->isGdImage(),
                $this->identicalTo(self::$sampleParams),
                $this->identicalTo(0x3FBBBBBB),
                $this->equalTo(20)
            );
            $obj->render($rasterizer, self::$sampleOptions, $node);
        }

        imagedestroy($rasterizer->getImage());
    }
}
