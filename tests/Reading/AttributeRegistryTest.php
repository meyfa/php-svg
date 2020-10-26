<?php

namespace SVG;

use SimpleXMLElement;
use SVG\Reading\AttributeRegistry;

/**
 * @SuppressWarnings(PHPMD)
 */
class AttributeRegistryTest extends \PHPUnit\Framework\TestCase
{
    public function testIsStyle()
    {
        $this->assertTrue(AttributeRegistry::isStyle('font-size'));
        $this->assertTrue(AttributeRegistry::isStyle('stroke'));
        $this->assertFalse(AttributeRegistry::isStyle('x'));
        $this->assertFalse(AttributeRegistry::isStyle('width'));
    }

    public function testConvertStyleAttribute()
    {
        $this->assertSame('42px', AttributeRegistry::convertStyleAttribute('font-size', '42'));
        $this->assertSame('42%', AttributeRegistry::convertStyleAttribute('font-size', '42%'));
        $this->assertSame('42px', AttributeRegistry::convertStyleAttribute('font-size', '42px'));
        $this->assertSame('42', AttributeRegistry::convertStyleAttribute('some-other-prop', '42'));
    }
}
