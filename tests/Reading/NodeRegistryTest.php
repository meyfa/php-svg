<?php

namespace SVG\Reading;

use SVG\Nodes\Shapes\SVGRect;
use SVG\Nodes\SVGGenericNodeType;

/**
 * @covers \SVG\Reading\NodeRegistry
 *
 * @SuppressWarnings(PHPMD)
 */
class NodeRegistryTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldConstructKnownTypes()
    {
        $result = NodeRegistry::create('rect');
        $this->assertInstanceOf(SVGRect::class, $result);
    }

    public function testShouldUseGenericTypeForOthers()
    {
        $result = NodeRegistry::create('div');
        $this->assertInstanceOf(SVGGenericNodeType::class, $result);
    }
}
