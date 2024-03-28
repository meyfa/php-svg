<?php

namespace SVG\Tests\Nodes\Texts;

use PHPUnit\Framework\TestCase;
use SVG\Nodes\Texts\SVGText;
use SVG\Rasterization\SVGRasterizer;

/**
 * @coversDefaultClass \SVG\Nodes\Texts\SVGText
 * @covers ::<!public>
 *
 * @SuppressWarnings(PHPMD)
 */
class SVGTextTest extends TestCase
{
    /**
     * @covers ::rasterize
     */
    public function testRasterizeShouldHaveDefaultFontSize(): void
    {
        // Test for https://github.com/meyfa/php-svg/issues/195

        $obj = new SVGText('foo', 10, 10);

        $rast = $this->getMockBuilder(SVGRasterizer::class)
            ->disableOriginalConstructor()
            ->getMock();

        // should call image renderer with correct options
        $rast->expects($this->once())->method('render')->with(
            $this->identicalTo('text'),
            $this->callback(fn ($options) => isset($options['fontSize']) && $options['fontSize'] === 16),
            $this->identicalTo($obj)
        );
        $obj->rasterize($rast);
    }
}
