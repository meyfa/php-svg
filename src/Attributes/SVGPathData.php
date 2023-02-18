<?php

namespace SVG\Attributes;

use SVG\Attributes\PathData\ClosePath;
use SVG\Attributes\PathData\Move;
use SVG\Attributes\PathData\PathDataInstructionInterface;
use SVG\Attributes\PathData\RelativeMove;

class SVGPathData implements SVGAttributeInterface, \Iterator, \Countable
{
    public const ATTRIBUTE_NAME = 'd';

    /**
     * @var class-string<PathDataInstructionInterface>[]
     */
    public static array $instructions = [
        Move::class,
        RelativeMove::class,
        ClosePath::class
    ];

    private ?PathDataInstructionInterface $head = null;

    private ?PathDataInstructionInterface $iteratorCurrent = null;

    public static function fromString(string $pathDataString): SVGPathData
    {
        $pathData = new static();

        $instructions = [];
        preg_match_all("/\s*([a-z])\s*([^a-z]+)*/i", $pathDataString, $instructions);

        foreach ($instructions[0] as $instructionString) {
            $instructionParts = explode(" ", $instructionString);
            $instructionParts = array_map(fn ($instruction) => trim($instruction), $instructionParts);
            $instructionName = array_shift($instructionParts);

            $instructionClass = null;

            foreach (self::$instructions as $instructionClassCandidate) {
                if (in_array($instructionName, $instructionClassCandidate::getNames())) {
                    $instructionClass = $instructionClassCandidate;
                    break;
                }
            }

            if (!$instructionClass) {
                throw new \RuntimeException(sprintf("Couldn't parse '%s' SVG path part", $instructionString));
            }

            $instruction = new $instructionClass(...$instructionParts);
            $pathData->addInstruction($instruction);
        }

        return $pathData;
    }

    public function getName(): string
    {
        return self::ATTRIBUTE_NAME;
    }

    public function __toString(): string
    {
        $d = [];

        $instruction = $this->head;

        while ($instruction) {
            array_unshift($d, $instruction->__toString());
            $instruction = $instruction->getPrevious();
        }

        return join(' ', $d);
    }

    public function addInstruction(PathDataInstructionInterface $instruction): self
    {
        if ($this->head) {
            $instruction->setPrevious($this->head);
        }
        $this->head = $instruction;

        return $this;
    }

    /**
     * @param callable(): PathDataInstructionInterface $transformator
     */
    public function transform(callable $transformator): void
    {
        $instruction = $this->head;
        // instruction following the currently looped one
        // initially it's empty, as we loop from end, and last instruction is not followed by anything
        $nextInstruction = null;

        while ($instruction) {
            $transformedInstruction = $instruction->transform($transformator);

            if ($transformedInstruction !== $instruction) {
                // if instruction returned a different instance it needs to be replaced in a chaing
                $transformedInstruction->setPrevious($instruction->getPrevious());
                $nextInstruction->setPrevious($transformedInstruction);
            }

            $nextInstruction = $instruction;
            $instruction = $instruction->getPrevious();
        }
    }

    public function rewind(): void
    {
        $this->iteratorCurrent = $this->head;
    }

    public function key(): int
    {
        $instruction = $this->head;
        $index = 0;

        while ($instruction) {
            $index--;
            $instruction = $instruction->getPrevious();
        }

        return $index;
    }

    public function current(): ?PathDataInstructionInterface
    {
        return $this->iteratorCurrent ?? $this->head;
    }

    public function next(): void
    {
        $this->iteratorCurrent = $this->current()?->getPrevious();
    }

    public function valid(): bool
    {
        return $this->iteratorCurrent !== null;
    }

    public function count(): int
    {
        $count = 0;

        $instruction = $this->head;

        while ($instruction) {
            $count++;
            $instruction = $instruction->getPrevious();
        }

        return $count;
    }
}
