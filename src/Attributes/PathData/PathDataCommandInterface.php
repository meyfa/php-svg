<?php

namespace SVG\Attributes\PathData;

interface PathDataCommandInterface
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

    public function getPrevious(): ?PathDataCommandInterface;

    public function setPrevious(?PathDataCommandInterface $previousInstruction): static;

    public function __toString(): string;

    /**
     * @return array{0: float, 1: float}  Array of coordinates of all used points
     */
    public function getPoints(): array;

    /**
     * @return array{0: float, 1: float}[]  Absolute coordinates of last point
     */
    public function getLastPoint(): array;

    /**
     * @param callable(): PathDataInstructionInterface $transformator
     */
    public function transform(callable $transformator): PathDataCommandInterface;
}
