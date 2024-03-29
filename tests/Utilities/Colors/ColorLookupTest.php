<?php

namespace SVG\Tests\Utilities\Colors;

use PHPUnit\Framework\TestCase;
use SVG\Utilities\Colors\ColorLookup;

/**
 * @covers \SVG\Utilities\Colors\ColorLookup
 *
 * @SuppressWarnings(PHPMD)
 */
class ColorLookupTest extends TestCase
{
    public function testGet(): void
    {
        // named colors
        $this->assertEquals([0, 0, 0, 255], ColorLookup::get('black'));
        $this->assertEquals([255, 255, 255, 255], ColorLookup::get('white'));
        $this->assertEquals([250, 128, 114, 255], ColorLookup::get('salmon'));

        // transparency
        $this->assertEquals([0, 0, 0, 0], ColorLookup::get('transparent'));

        // invalid color name
        $this->assertNull(ColorLookup::get('doesnotexist'));
    }
}
