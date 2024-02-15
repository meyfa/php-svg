<?php

namespace SVG\Nodes\Texts;

use SVG\Rasterization\SVGRasterizer;

/**
 * @coversDefaultClass \SVG\Nodes\Texts\SVGText
 * @covers ::<!public>
 *
 * @SuppressWarnings(PHPMD)
 */
class SVGTextTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers ::rasterize
     */
    public function testRasterizeShouldHaveDefaultFontSize()
    {
        // Test for https://github.com/meyfa/php-svg/issues/195

        $obj = new SVGText('foo', 10, 10);

        $rast = $this->getMockBuilder(SVGRasterizer::class)
            ->disableOriginalConstructor()
            ->getMock();

        // should call image renderer with correct options
        $rast->expects($this->once())->method('render')->with(
            $this->identicalTo('text'),
            $this->callback(function ($options) {
                return isset($options['fontSize']) && $options['fontSize'] === 16;
            }),
            $this->identicalTo($obj)
        );
        $obj->rasterize($rast);
    }
}
