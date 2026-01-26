<?php

declare(strict_types=1);

namespace Intentio\Domain\Blueprint;

interface BlueprintRepository
{
    /**
     * Finds a blueprint by its name.
     *
     * @param string $name The name of the blueprint.
     * @return Blueprint|null The found blueprint or null if not found.
     */
    public function findByName(string $name): ?Blueprint;

    /**
     * Finds all available blueprints.
     *
     * @return Blueprint[] An array of all found blueprints.
     */
    public function findAll(): array;
}
