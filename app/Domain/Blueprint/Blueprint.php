<?php

declare(strict_types=1);

namespace Intentio\Domain\Blueprint;

final class Blueprint
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
}
