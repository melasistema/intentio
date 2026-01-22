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
    private ?string $command = null;
    private array $options = [];
    private array $arguments = [];

    public function __construct(array $argv)
    {
        // Remove script name
        $cliArgs = array_slice($argv, 1);

        if (empty($cliArgs)) {
            return; // No command or arguments provided
        }

        // The first non-option argument is the command
        $commandFound = false;
        foreach ($cliArgs as $arg) {
            if (str_starts_with($arg, '--')) {
                // This is an option
                $parts = explode('=', $arg, 2);
                $optionName = substr($parts[0], 2);
                $this->options[$optionName] = $parts[1] ?? 'true';
            } elseif (!$commandFound) {
                // This is the command
                $this->command = $arg;
                if (str_starts_with($this->command, '/')) {
                    $this->command = ltrim($this->command, '/');
                }
                $commandFound = true;
            } else {
                // This is a positional argument
                $this->arguments[] = $arg;
            }
        }
    }

    public function getCommand(): ?string
    {
        return $this->command;
    }

    public function getArgument(int $index): ?string
    {
        return $this->arguments[$index] ?? null;
    }

    public function getOption(string $name): ?string
    {
        return $this->options[$name] ?? null;
    }
}
