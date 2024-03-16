<?php

namespace SVG\Tests\Reading;

use PHPUnit\Framework\TestCase;
use SVG\Reading\AttributeRegistry;

/**
 * @coversDefaultClass \SVG\Reading\AttributeRegistry
 * @covers ::<!public>
 *
 * @SuppressWarnings(PHPMD)
 */
class AttributeRegistryTest extends TestCase
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
