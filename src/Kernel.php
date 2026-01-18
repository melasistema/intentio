<?php

declare(strict_types=1);

namespace Intentio;

use Intentio\Cli\Input;
use Intentio\Cli\Output;
use Intentio\Cli\Help;
use Intentio\Command\ChatCommand;
use Intentio\Knowledge\Space;

/**
 * The core orchestrator of the INTENTIO cognitive environment.
 *
 * Elegance is the primary requirement. This class avoids magic and
 * promotes an explicit, readable control flow. It connects the
 * different components of the system in a clear, deterministic way.
 */
final class Kernel
{
    private array $commands = [
        'chat' => ChatCommand::class,
        'ingest' => \Intentio\Command\IngestCommand::class,
        'interact' => \Intentio\Command\InteractCommand::class,
        'status' => \Intentio\Command\StatusCommand::class,
    ];

    public function __construct(
        private readonly array $config,
        private readonly Input $input
    ) {
    }

    public function run(): void
    {
        $appName = $this->config['app_name'] ?? 'INTENTIO';
        Output::writeln("Welcome to {$appName}.");

        $commandName = $this->input->getCommand();

        if ($commandName === 'help' || $commandName === null) {
            Help::display();
            return;
        }

        // Determine the knowledge space to use
        $knowledgeSpacePath = $this->input->getOption('space');
        if (empty($knowledgeSpacePath)) {
            $knowledgeSpacePath = $this->config['knowledge_base_path'];
        }

        $knowledgeSpace = null;
        try {
            $knowledgeSpace = new Space($knowledgeSpacePath);
        } catch (\InvalidArgumentException $e) {
            Output::error("Error initializing knowledge space: " . $e->getMessage());
            Output::error("Please ensure the path '{$knowledgeSpacePath}' is valid.");
            exit(1);
        }

        if (isset($this->commands[$commandName])) {
            $commandClass = $this->commands[$commandName];
            // Instantiate the command and execute it
            $command = new $commandClass(
                input: $this->input,
                config: $this->config,
                knowledgeSpace: $knowledgeSpace
            );
            $exitCode = $command->execute();
            exit($exitCode);
        } else {
            Output::writeln("Unknown command: '{$commandName}'.");
            Help::display();
            exit(1);
        }

        Output::writeln("INTENTIO session ended.");
    }
}
