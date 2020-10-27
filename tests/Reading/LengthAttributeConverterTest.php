<?php

namespace SVG;

use SVG\Reading\LengthAttributeConverter;

/**
 * @SuppressWarnings(PHPMD)
 */
class LengthAttributeConverterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers \SVG\Reading\LengthAttributeConverter
     */
    public function testShouldQualifyUnitlessNumbers()
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

    /**
     * @covers \SVG\Reading\LengthAttributeConverter
     */
    public function testShouldTrimWhitespace()
    {
        $obj = LengthAttributeConverter::getInstance();

        $this->assertSame('-42.123px', $obj->convert('  -42.123'));
        $this->assertSame('-42.123px', $obj->convert('-42.123  '));
        $this->assertSame('-42.123px', $obj->convert(" \n -42.123 \n "));
    }

    /**
     * @covers \SVG\Reading\LengthAttributeConverter
     */
    public function testShouldIgnoreOtherValues()
    {
        $obj = LengthAttributeConverter::getInstance();

        $this->assertSame('42%', $obj->convert('42%'));
        $this->assertSame('42px', $obj->convert('42px'));
        $this->assertSame('none', $obj->convert('none'));
        $this->assertSame('42 37', $obj->convert('42 37'));
    }
}
