<?php

declare(strict_types=1);

namespace Intentio\Domain\Space;

interface SpaceRepository
{
    public function findByName(string $name): ?Space;
    public function save(Space $space): void;
    public function exists(string $name): bool;
    public function findAll(): array;
    public function delete(string $name): void;
}
