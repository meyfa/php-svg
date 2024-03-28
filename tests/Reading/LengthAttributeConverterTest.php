<?php

namespace SVG\Tests\Reading;

use PHPUnit\Framework\TestCase;
use SVG\Reading\LengthAttributeConverter;

/**
 * @covers \SVG\Reading\LengthAttributeConverter
 *
 * @SuppressWarnings(PHPMD)
 */
class LengthAttributeConverterTest extends TestCase
{
    public function testShouldQualifyUnitlessNumbers(): void
    {
        $obj = LengthAttributeConverter::getInstance();

        $this->assertSame('42px', $obj->convert('42'));
        $this->assertSame('+42px', $obj->convert('+42'));
        $this->assertSame('-42px', $obj->convert('-42'));
        $this->assertSame('42.123px', $obj->convert('42.123'));
        $this->assertSame('-.123px', $obj->convert('-.123'));
        $this->assertSame('-42.px', $obj->convert('-42.'));
        $this->assertSame('-42.123px', $obj->convert('-42.123'));
    }

    public function testShouldTrimWhitespace(): void
    {
        $obj = LengthAttributeConverter::getInstance();

        $this->assertSame('-42.123px', $obj->convert('  -42.123'));
        $this->assertSame('-42.123px', $obj->convert('-42.123  '));
        $this->assertSame('-42.123px', $obj->convert(" \n -42.123 \n "));
    }

    public function testShouldIgnoreOtherValues(): void
    {
        $obj = LengthAttributeConverter::getInstance();

        $this->assertSame('42%', $obj->convert('42%'));
        $this->assertSame('42px', $obj->convert('42px'));
        $this->assertSame('none', $obj->convert('none'));
        $this->assertSame('42 37', $obj->convert('42 37'));
    }
}
