<?php

declare(strict_types=1);

namespace Intentio\Cli;

/**
 * A simple, dependency-free helper for writing to the console.
 *
 * Adheres to the principle of being "quiet by design".
 * Output should be intentional and clear.
 */
final class Output
{
    /**
     * Writes a message to the standard output, followed by a newline.
     */
    public static function writeln(string $message): void
    {
        echo $message . PHP_EOL;
    }

    /**
     * Writes an error message to the standard error output.
     */
    public static function error(string $message): void
    {
        file_put_contents('php://stderr', "Error: " . $message . PHP_EOL);
    }

    /**
     * Writes a success message to the standard output.
     */
    public static function success(string $message): void
    {
        echo "Success: " . $message . PHP_EOL;
    }

    /**
     * Writes an informational message to the standard output.
     */
    public static function info(string $message): void
    {
        echo "Info: " . $message . PHP_EOL;
    }
}
