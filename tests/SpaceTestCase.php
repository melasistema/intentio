<?php

declare(strict_types=1);

namespace Intentio\Tests;

use Intentio\Domain\Space\Space;
use PHPUnit\Framework\TestCase;

/**
 * A test that needs a cognitive space on disk. The space lives in the system's temporary folder
 * and is deleted after each test, so no test touches the real spaces.
 */
abstract class SpaceTestCase extends TestCase
{
    protected Space $space;

    protected function setUp(): void
    {
        $path = sys_get_temp_dir() . '/intentio_test_' . bin2hex(random_bytes(6));
        mkdir($path . '/knowledge', 0777, true);
        mkdir($path . '/prompts', 0777, true);

        $this->space = new Space('test_space', $path);
    }

    protected function tearDown(): void
    {
        $this->deleteDirectory($this->space->getPath());
    }

    /**
     * Writes a file inside the space, creating its folders.
     *
     * @param string $relativePath The file's path inside the space, e.g. 'knowledge/frameworks/lean_canvas.md'.
     * @return string The full path of the file.
     */
    protected function writeFile(string $relativePath, string $content): string
    {
        $path = $this->space->getPath() . '/' . $relativePath;
        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }
        file_put_contents($path, $content);

        return $path;
    }

    private function deleteDirectory(string $directory): void
    {
        foreach (scandir($directory) as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $path = $directory . '/' . $entry;
            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                unlink($path);
            }
        }
        rmdir($directory);
    }
}
