<?php

declare(strict_types=1);

namespace Intentio\Domain\Space;

use Intentio\Shared\Exceptions\IntentioException;

final class SpaceFactory
{
    /**
     * Creates a new Space domain object and ensures its root directory exists.
     * The actual sub-structure (e.g., knowledge/, prompts/) is expected to be
     * handled by the blueprint copying process.
     *
     * @param string $name The name of the new space.
     * @param string $path The full path where the space's root directory will be created.
     * @return Space The newly created Space object.
     * @throws IntentioException If the space root directory cannot be created.
     */
    public function createSpace(string $name, string $path): Space
    {
        if (!is_dir($path)) {
            if (!mkdir($path, 0777, true)) {
                throw new IntentioException("Could not create space root directory: {$path}");
            }
        }

        // Removed creation of fixed subdirectories (reference, memory, opinion, prompts).
        // These are now expected to be part of the blueprint structure.

        return new Space($name, $path);
    }
}