<?php

namespace SVG\Attributes\PathData;

interface PathDataInstructionInterface
{
    public function getName(): string;

    public function requiresPrevious(): bool;

    public function getPrevious(): ?PathDataInstructionInterface;

    public function setPrevious(?PathDataInstructionInterface $previousInstruction): static;

    public function __toString(): string;

    /**
     * @return array{0: float, 1: float}
     */
    public function getPoints(): array;

    /**
     * @return array{0: float, 1: float}[]
     */
    public function getLastPoint(): array;

    public function transform(callable $transformator): PathDataInstructionInterface;
}
