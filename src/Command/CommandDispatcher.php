<?php

declare(strict_types=1);

namespace Intentio\Command;

use Intentio\Cli\Input;
use Intentio\Cli\Output;

/**
 * Dispatches CLI commands based on command name and resolves their arguments.
 *
 * This class centralizes the mapping of command names to command classes
 * and uses a CommandArgumentResolver to instantiate commands with their
 * correct dependencies, making the Kernel thinner.
 */
final class CommandDispatcher
{
    private array $commands = [
        'chat' => ChatCommand::class,
        'ingest' => IngestCommand::class,
        'interact' => InteractCommand::class,
        'status' => StatusCommand::class,
        'clear' => ClearCommand::class,
        'init' => InitCommand::class,
        // Add other commands here
    ];

    public function __construct(
        private readonly CommandArgumentResolver $resolver,
        private readonly array $config,
        private readonly Input $input // Pass input directly for specific command needs
    ) {
    }

    /**
     * Dispatches a command by name.
     *
     * @param string $commandName The name of the command to dispatch.
     * @param array $commandConfig Specific configuration or runtime data required by the command.
     *                             (e.g., an instantiated Knowledge\Space object or knowledgeBasePath string).
     * @return int The exit code of the executed command.
     * @throws \RuntimeException If the command is not found or arguments cannot be resolved.
     */
    public function dispatch(string $commandName, array $commandConfig = []): int
    {
        if (!isset($this->commands[$commandName])) {
            throw new \RuntimeException("Command '{$commandName}' not registered.");
        }

        $commandClass = $this->commands[$commandName];

        try {
            // Resolve arguments for the command's constructor
            $args = $this->resolver->resolve($commandClass, $commandConfig);
            
            // Instantiate and execute the command
            $command = new $commandClass(...$args);
            return $command->execute();
        } catch (\Throwable $e) {
            Output::error("Error executing command '{$commandName}': " . $e->getMessage());
            return 1; // Indicate failure
        }
    }
}
