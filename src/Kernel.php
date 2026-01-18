<?php

declare(strict_types=1);

namespace Intentio;

use Intentio\Cli\Input;
use Intentio\Cli\Output;
use Intentio\Cli\Help;
use Intentio\Command\ChatCommand;
use Intentio\Command\IngestCommand;
use Intentio\Command\InteractCommand;
use Intentio\Command\StatusCommand;
use Intentio\Command\ClearCommand;
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
        'ingest' => IngestCommand::class,
        'interact' => InteractCommand::class,
        'status' => StatusCommand::class,
        'clear' => ClearCommand::class,
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

        $knowledgeBasePath = $this->config['knowledge_base_path'];
        $spaceOption = $this->input->getOption('space');
        
        // --- Command-specific handling of knowledge space ---
        switch ($commandName) {
            case 'chat':
            case 'ingest':
            case 'clear':
                // These commands require a specific cognitive space, not the base container.
                if (empty($spaceOption)) {
                    Output::error("Error: Command '{$commandName}' requires a specific cognitive space.");
                    Output::error("Please use --space=<path/to/your/space> (e.g., --space=knowledge/my_personal_agent).");
                    exit(1);
                }
                
                $fullSpacePath = $spaceOption;
                // Ensure the provided space path is within the configured knowledge_base_path for safety/consistency
                if (!str_starts_with(realpath($fullSpacePath), realpath($knowledgeBasePath))) {
                     Output::error("Error: The specified knowledge space '{$spaceOption}' is not within the configured knowledge base path '{$knowledgeBasePath}'.");
                     exit(1);
                }

                try {
                    $knowledgeSpace = new Space($fullSpacePath);
                } catch (\InvalidArgumentException $e) {
                    Output::error("Error initializing cognitive space '{$fullSpacePath}': " . $e->getMessage());
                    exit(1);
                }
                $commandArgs = [
                    'input' => $this->input,
                    'config' => $this->config,
                    'knowledgeSpace' => $knowledgeSpace
                ];
                break;

            case 'interact':
            case 'status':
                // These commands need the base knowledge path to list available spaces.
                $commandArgs = [
                    'input' => $this->input,
                    'config' => $this->config,
                    'knowledgeBasePath' => $knowledgeBasePath // Pass base path directly
                ];
                break;

            default:
                Output::writeln("Unknown command: '{$commandName}'.");
                Help::display();
                exit(1);
        }
        // --- End command-specific handling ---

        if (isset($this->commands[$commandName])) {
            $commandClass = $this->commands[$commandName];
            // Instantiate the command and execute it
            $command = new $commandClass(...$commandArgs); // Use spread operator for args
            $exitCode = $command->execute();
            exit($exitCode);
        } else {
            // This case should ideally not be reached due to the switch above
            Output::writeln("Unknown command: '{$commandName}'.");
            Help::display();
            exit(1);
        }

        // Output::writeln("INTENTIO session ended."); // This line is now redundant as commands exit directly
    }
}
