<?php

declare(strict_types=1);

namespace Intentio\Cli;

/**
 * A simple helper to parse command-line arguments.
 *
 * This class provides a basic, explicit way to access arguments
 * passed to the script, avoiding global arrays directly.
 */
final class Input
{
    private array $args;
    private ?string $command;

    public function __construct(array $argv)
    {
        // The first argument is the script name, so we discard it.
        $this->args = array_slice($argv, 1);
        $this->command = $this->args[0] ?? null;
    }

    public function getCommand(): ?string
    {
        return $this->command;
    }

    public function getArgument(int $index): ?string
    {
        // Adjust index to account for the command being arg 0.
        return $this->args[$index + 1] ?? null;
    }

    public function getOption(string $name): ?string
    {
        // Support --option=value and --option
        foreach ($this->args as $arg) {
            if (str_starts_with($arg, "--{$name}=")) {
                return substr($arg, strlen("--{$name}="));
            }
            if ($arg === "--{$name}") {
                return 'true'; // Indicate presence of a boolean flag
            }
        }
        return null;
    }
}
