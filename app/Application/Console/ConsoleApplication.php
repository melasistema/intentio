<?php

declare(strict_types=1);

namespace Intentio\Application\Console;

use Intentio\Application\Console\Commands\CommandInterface;
use Intentio\Shared\Exceptions\IntentioException;

final class ConsoleApplication
{
    private string $name;
    private string $version;
    /** @var CommandInterface[] */
    private array $commands = [];
    private array $argv;

    public function __construct(string $name, string $version, ?array $argv = null)
    {
        $this->name = $name;
        $this->version = $version;
        $this->argv = $argv ?? $_SERVER['argv'];
    }

    public function addCommand(CommandInterface $command): void
    {
        $this->commands[$command->getName()] = $command;
    }

    public function run(): int
    {
        $input = $this->parseInput();
        $commandName = $input['command'] ?? 'help';

        if ($commandName === 'help' || !isset($this->commands[$commandName])) {
            $this->displayHelp();
            return 0;
        }

        try {
            $command = $this->commands[$commandName];
            return $command->execute($input['arguments'], $input['options']);
        } catch (IntentioException $e) {
            $this->outputError("Error: " . $e->getMessage());
            return 1;
        } catch (\Throwable $e) {
            $this->outputError("An unexpected error occurred: " . $e->getMessage());
            return 1;
        }
    }

    private function parseInput(): array
    {
        $command = null;
        $arguments = [];
        $options = [];

        foreach ($this->argv as $index => $arg) {
            if ($index === 0) { // Script name
                continue;
            }

            if (str_starts_with($arg, '--')) {
                // Option: --name=value or --name
                $parts = explode('=', substr($arg, 2), 2);
                $options[$parts[0]] = $parts[1] ?? true;
            } elseif (str_starts_with($arg, '-')) {
                // Short option: -n value or -n
                // Currently not supported, but can be added later
                continue;
            } elseif ($command === null) {
                // First non-option argument is the command name
                $command = $arg;
            } else {
                // Subsequent non-option arguments are command arguments
                $arguments[] = $arg;
            }
        }

        return [
            'command' => $command,
            'arguments' => $arguments,
            'options' => $options,
        ];
    }

    private function displayHelp(): void
    {
        fwrite(STDOUT, "{$this->name} v{$this->version}" . PHP_EOL);
        fwrite(STDOUT, PHP_EOL);
        fwrite(STDOUT, "Usage: php intentio <command> [arguments] [--options]" . PHP_EOL);
        fwrite(STDOUT, PHP_EOL);
        fwrite(STDOUT, "Available commands:" . PHP_EOL);
        foreach ($this->commands as $command) {
            fwrite(STDOUT, sprintf("  %-15s %s" . PHP_EOL, $command->getName(), $command->getDescription()));
        }
        fwrite(STDOUT, PHP_EOL);
        fwrite(STDOUT, "Use 'help' to see this message." . PHP_EOL);
    }

    private function outputError(string $message): void
    {
        // For CLI, output to STDERR and potentially colorize
        fwrite(STDERR, "\033[31m" . $message . "\033[0m" . PHP_EOL); // Red color
    }
}
