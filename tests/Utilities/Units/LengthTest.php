<?php

namespace SVG\Tests\Utilities\Units;

use PHPUnit\Framework\TestCase;
use SVG\Utilities\Units\Length;

/**
 * @covers \SVG\Utilities\Units\Length
 *
 * @SuppressWarnings(PHPMD)
 */
class LengthTest extends TestCase
{
    public function testConvert(): void
    {
        // units
        $this->assertEquals(16, Length::convert('12pt', 100));
        $this->assertEquals(16, Length::convert('1pc', 100));
        $this->assertEqualsWithDelta(37.8, Length::convert('1cm', 100), 0.01);
        $this->assertEqualsWithDelta(37.8, Length::convert('10mm', 100), 0.01);
        $this->assertEquals(96, Length::convert('1in', 100));
        $this->assertEquals(50, Length::convert('50%', 100));
        $this->assertEquals(16, Length::convert('16px', 100));

        // no unit
        $this->assertEquals(16, Length::convert('16', 100));

        // number
        $this->assertEquals(16, Length::convert(16, 100));

        // illegal: missing number
        $this->assertNull(Length::convert('px', 100));
        $this->assertNull(Length::convert('', 100));
        $this->assertNull(Length::convert(null, 100));
    }
}
