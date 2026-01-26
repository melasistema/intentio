<?php

declare(strict_types=1);

namespace Intentio\Domain\Space;

final class Space
{
    public function __construct(
        private string $name,
        private string $path
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    // You can add methods here to access internal paths like reference/, memory/, etc.
    // e.g., public function getReferencePath(): string { return $this->path . '/reference'; }
    public function getReferencePath(): string
    {
        return $this->path . '/reference';
    }

    public function getMemoryPath(): string
    {
        return $this->path . '/memory';
    }

    public function getOpinionPath(): string
    {
        return $this->path . '/opinion';
    }

    public function getPromptsPath(): string
    {
        return $this->path . '/prompts';
    }
}
