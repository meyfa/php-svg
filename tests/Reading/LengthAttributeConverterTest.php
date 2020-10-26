<?php

namespace SVG;

use SVG\Reading\LengthAttributeConverter;

/**
 * @SuppressWarnings(PHPMD)
 */
class LengthAttributeConverterTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldQualifyUnitlessNumbers()
    {
        $this->assertSame('42px', LengthAttributeConverter::getInstance()->convert('42'));
        $this->assertSame('+42px', LengthAttributeConverter::getInstance()->convert('+42'));
        $this->assertSame('-42px', LengthAttributeConverter::getInstance()->convert('-42'));
        $this->assertSame('42.123px', LengthAttributeConverter::getInstance()->convert('42.123'));
        $this->assertSame('-.123px', LengthAttributeConverter::getInstance()->convert('-.123'));
        $this->assertSame('-42.px', LengthAttributeConverter::getInstance()->convert('-42.'));
        $this->assertSame('-42.123px', LengthAttributeConverter::getInstance()->convert('-42.123'));
    }

    public function testShouldTrimWhitespace()
    {
        $this->assertSame('-42.123px', LengthAttributeConverter::getInstance()->convert('  -42.123'));
        $this->assertSame('-42.123px', LengthAttributeConverter::getInstance()->convert('-42.123  '));
        $this->assertSame('-42.123px', LengthAttributeConverter::getInstance()->convert(" \n -42.123 \n "));
    }

    public function testShouldIgnoreOtherValues()
    {
        $this->assertSame('42%', LengthAttributeConverter::getInstance()->convert('42%'));
        $this->assertSame('42px', LengthAttributeConverter::getInstance()->convert('42px'));
        $this->assertSame('none', LengthAttributeConverter::getInstance()->convert('none'));
        $this->assertSame('42 37', LengthAttributeConverter::getInstance()->convert('42 37'));
    }
}
