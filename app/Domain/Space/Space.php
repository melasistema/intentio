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

    public function getPromptsPath(): string
    {
        return $this->path . '/prompts';
    }

    public function getKnowledgePath(): string
    {
        return $this->path . '/knowledge';
    }
}