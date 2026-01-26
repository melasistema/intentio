<?php

declare(strict_types=1);

namespace Intentio\Domain\Space;

use Intentio\Shared\Exceptions\IntentioException;

final class SpaceFactory
{
    /**
     * Creates a new Space domain object and its essential directory structure.
     *
     * @param string $name The name of the new space.
     * @param string $path The full path where the space's root directory will be created.
     * @return Space The newly created Space object.
     * @throws IntentioException If the space directory or its subdirectories cannot be created.
     */
    public function createSpace(string $name, string $path): Space
    {
        if (!is_dir($path)) {
            if (!mkdir($path, 0777, true)) {
                throw new IntentioException("Could not create space directory: {$path}");
            }
        }

        // Create essential subdirectories for the cognitive space
        $subdirectories = ['reference', 'memory', 'opinion', 'prompts'];
        foreach ($subdirectories as $subdir) {
            $subdirPath = $path . DIRECTORY_SEPARATOR . $subdir;
            if (!is_dir($subdirPath)) {
                if (!mkdir($subdirPath, 0777, true)) {
                    throw new IntentioException("Could not create space subdirectory: {$subdirPath}");
                }
            }
        }

        return new Space($name, $path);
    }
}
