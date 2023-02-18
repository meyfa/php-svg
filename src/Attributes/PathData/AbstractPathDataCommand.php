<?php

namespace SVG\Attributes\PathData;

abstract class AbstractPathDataCommand implements PathDataCommandInterface
{
    private ?PathDataCommandInterface $previous = null;

    protected bool $requiresPrevious = false;

    public function requiresPrevious(): bool
    {
        return $this->requiresPrevious;
    }

    public function getPrevious(): ?PathDataCommandInterface
    {
        if ($this->requiresPrevious() && !isset($this->previous)) {
            throw new \LogicException(sprintf("This %s command requires previous element to be set!", get_class($this)));
        }

        return $this->previous;
    }

    public function setPrevious(?PathDataCommandInterface $previous): static
    {
        $this->previous = $previous;

        return $this;
    }
}
