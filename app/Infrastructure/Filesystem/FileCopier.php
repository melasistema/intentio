<?php

declare(strict_types=1);

namespace Intentio\Infrastructure\Filesystem;

use Intentio\Shared\Exceptions\IntentioException;

final class FileCopier
{
    /**
     * Recursively copies a directory from source to destination.
     *
     * @param string $source The source directory path.
     * @param string $destination The destination directory path.
     * @param array $exclude Optional array of filenames/directories to exclude from copying.
     * @throws IntentioException If source or destination paths are invalid or copying fails.
     */
    public function copyDirectory(string $source, string $destination, array $exclude = []): void
    {
        if (!is_dir($source)) {
            throw new IntentioException("Source directory does not exist: {$source}");
        }

        // Create destination directory if it doesn't exist
        if (!is_dir($destination) && !mkdir($destination, 0777, true)) {
            throw new IntentioException("Could not create destination directory: {$destination}");
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $relativePath = $iterator->getSubPathName();

            // Check if the item should be excluded
            if (in_array(basename($relativePath), $exclude, true)) {
                continue;
            }

            $destPath = $destination . DIRECTORY_SEPARATOR . $relativePath;

            if ($item->isDir()) {
                if (!is_dir($destPath) && !mkdir($destPath, 0777, true)) {
                    throw new IntentioException("Could not create directory: {$destPath}");
                }
            } else {
                if (!copy($item->getPathname(), $destPath)) {
                    throw new IntentioException("Could not copy file from {$item->getPathname()} to {$destPath}");
                }
            }
        }
    }
}
