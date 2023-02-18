<?php

namespace SVG\Attributes\PathData;

interface PathDataInstructionInterface
{
    /**
     * Gets the name of this class can handle
     *
     * @return string[]
     */
    public static function getNames(): array;

    /**
     * Gets the name of the current instruction
     */
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

    /**
     * @param callable(): PathDataInstructionInterface $transformator
     */
    public function transform(callable $transformator): PathDataInstructionInterface;
}
