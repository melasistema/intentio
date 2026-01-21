<?php

declare(strict_types=1);

namespace Intentio\Package;

use Intentio\Package\PackageInterface;
use Intentio\Cli\Output;
class Package implements PackageInterface
{
    private string $name;
    private string $sourcePath;
    private string $destinationPath;

    public function __construct(string $name, string $sourcePath, string $destinationPath)
    {
        $this->name = $name;
        $this->sourcePath = $sourcePath;
        $this->destinationPath = $destinationPath;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPath(): string
    {
        return $this->sourcePath;
    }

    public function init(): int
    {
        $packageDestinationDir = $this->destinationPath . '/' . $this->name;

        if (!is_dir($packageDestinationDir)) {
            if (!mkdir($packageDestinationDir, 0777, true)) {
                Output::error("Failed to create package destination directory: " . $packageDestinationDir);
                return 1;
            }
        }

        $subdirectoriesToCopy = ['knowledge', 'generators', 'prompts'];

        foreach ($subdirectoriesToCopy as $subdir) {
            $sourceSubdir = $this->sourcePath . '/' . $subdir;
            $destinationSubdir = $packageDestinationDir . '/' . $subdir;

            if (is_dir($sourceSubdir)) {
                if (!$this->copyDirectory($sourceSubdir, $destinationSubdir)) {
                    Output::error("Failed to copy " . $subdir . " for package " . $this->name);
                    return 1;
                }
            } else {
                Output::info("Warning: " . $subdir . " directory not found in package " . $this->name);
            }
        }

        Output::success("Package '" . $this->name . "' initialized successfully to " . $packageDestinationDir);
        return 0;
    }

    private function copyDirectory(string $source, string $destination): bool
    {
        if (!is_dir($destination)) {
            if (!mkdir($destination, 0777, true)) {
                return false;
            }
        }

        $iterator = new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS);
        $recursiveIterator = new \RecursiveIteratorIterator($iterator, \RecursiveIteratorIterator::SELF_FIRST);

        foreach ($recursiveIterator as $item) {
            $path = $destination . '/' . $recursiveIterator->getSubPathName();
            if ($item->isDir()) {
                if (!is_dir($path)) {
                    if (!mkdir($path, 0777, true)) {
                        return false;
                    }
                }
            } else {
                if (!copy($item->getPathName(), $path)) {
                    return false;
                }
            }
        }
        return true;
    }
}
