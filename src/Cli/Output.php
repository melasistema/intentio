<?php

declare(strict_types=1);

namespace Intentio\Cli;

/**
 * Handles all console output.
 *
 * This class centralizes output to ensure consistency across the CLI
 * and allows for easy formatting (e.g., colors) and suppression.
 */
final class Output
{
    private static bool $isQuiet = false;

    public static function writeln(string $message = ''): void
    {
        if (!self::$isQuiet) {
            echo $message . PHP_EOL;
        }
    }

    public static function error(string $message): void
    {
        self::writeln(self::colorize($message, 'red'));
    }

    public static function info(string $message): void
    {
        self::writeln(self::colorize($message, 'blue'));
    }

    public static function success(string $message): void
    {
        self::writeln(self::colorize($message, 'green'));
    }

    public static function warning(string $message): void
    {
        self::writeln(self::colorize($message, 'yellow'));
    }

    public static function orange(string $message): void
    {
        self::writeln(self::colorize($message, 'orange'));
    }

    public static function setQuiet(bool $isQuiet): void
    {
        self::$isQuiet = $isQuiet;
    }

    private static function colorize(string $text, string $color): string
    {
        $colors = [
            'red' => "\033[31m",
            'green' => "\033[32m",
            'yellow' => "\033[33m",
            'blue' => "\033[34m",
            'orange' => "\033[33m",
            'reset' => "\033[0m",
        ];

        return ($colors[$color] ?? '') . $text . ($colors['reset'] ?? '');
    }
}