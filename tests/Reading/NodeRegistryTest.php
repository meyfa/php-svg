<?php

namespace SVG\Tests\Reading;

use PHPUnit\Framework\TestCase;
use SVG\Nodes\Shapes\SVGRect;
use SVG\Nodes\SVGGenericNodeType;
use SVG\Reading\NodeRegistry;

/**
 * @covers \SVG\Reading\NodeRegistry
 *
 * @SuppressWarnings(PHPMD)
 */
class NodeRegistryTest extends TestCase
{
    public function testShouldConstructKnownTypes(): void
    {
        $result = NodeRegistry::create('rect');
        $this->assertInstanceOf(SVGRect::class, $result);
    }

    public function testShouldUseGenericTypeForOthers(): void
    {
        $result = NodeRegistry::create('div');
        $this->assertInstanceOf(SVGGenericNodeType::class, $result);
    }
}
