<?php

namespace SVG\Reading;

/**
 * @coversDefaultClass \SVG\Reading\AttributeRegistry
 * @covers ::<!public>
 *
 * @SuppressWarnings(PHPMD)
 */
class AttributeRegistryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers ::isStyle
     */
    public function testIsStyle(): void
    {
        $this->assertTrue(AttributeRegistry::isStyle('font-size'));
        $this->assertTrue(AttributeRegistry::isStyle('stroke'));
        $this->assertFalse(AttributeRegistry::isStyle('x'));
        $this->assertFalse(AttributeRegistry::isStyle('width'));
    }

    /**
     * @covers ::convertStyleAttribute
     */
    public function testConvertStyleAttribute(): void
    {
        $this->assertSame('42px', AttributeRegistry::convertStyleAttribute('font-size', '42'));
        $this->assertSame('42%', AttributeRegistry::convertStyleAttribute('font-size', '42%'));
        $this->assertSame('42px', AttributeRegistry::convertStyleAttribute('font-size', '42px'));
        $this->assertSame('42', AttributeRegistry::convertStyleAttribute('some-other-prop', '42'));
    }
}
