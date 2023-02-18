<?php

namespace SVG\Attributes\PathData;

abstract class AbstractPathDataInstruction implements PathDataInstructionInterface
{
    private ?PathDataInstructionInterface $previous = null;

    protected bool $requiresPrevious = false;

    public function requiresPrevious(): bool
    {
        return $this->requiresPrevious;
    }

    public function getPrevious(): ?PathDataInstructionInterface
    {
        if ($this->requiresPrevious() && !isset($this->previous)) {
            throw new \LogicException(sprintf("This %s instruction requires previous element to be set!", get_class($this)));
        }

        return $this->previous;
    }

    public function setPrevious(?PathDataInstructionInterface $previousInstruction): static
    {
        $this->previous = $previousInstruction;

        return $this;
    }
}
