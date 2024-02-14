<?php

namespace SVG;

use SVG\Reading\NodeRegistry;

/**
 * @covers \SVG\Reading\NodeRegistry
 *
 * @SuppressWarnings(PHPMD)
 */
class NodeRegistryTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldConstructKnownTypes(): void
    {
        $result = NodeRegistry::create('rect');
        $this->assertInstanceOf(\SVG\Nodes\Shapes\SVGRect::class, $result);
    }

    public function testShouldUseGenericTypeForOthers(): void
    {
        $result = NodeRegistry::create('div');
        $this->assertInstanceOf(\SVG\Nodes\SVGGenericNodeType::class, $result);
    }
}
