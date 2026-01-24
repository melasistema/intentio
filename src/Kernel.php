<?php

declare(strict_types=1);

namespace Intentio;

use Intentio\Cli\Input;
use Intentio\Cli\Output;
use Intentio\Cli\Help;
use Intentio\Command\CommandArgumentResolver;
use Intentio\Command\CommandDispatcher;
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
    public function __construct(
        private readonly array $config,
        private readonly Input $input
    ) {
    }

    public function run(): void
    {
        $appName = $this->config['app_name'] ?? 'INTENTIO';
        Output::orange("Welcome to {$appName}. Type 'help' for available commands.");

        $commandName = $this->input->getCommand();

        if ($commandName === 'help' || $commandName === null) {
            Help::display();
            return;
        }

        // Initialize the argument resolver and dispatcher
        $argumentResolver = new CommandArgumentResolver($this->input, $this->config);
        $commandDispatcher = new CommandDispatcher($argumentResolver, $this->config, $this->input);

        $spacesBasePath = $this->config['spaces_base_path'];
        $spaceOption = $this->input->getOption('space');
        
        $commandConfig = []; // Collect command-specific arguments for resolver

        // --- Command-specific handling of knowledge space (moved to CommandDispatcher) ---
        // This logic is still here in Kernel because Kernel decides *what* to pass to CommandDispatcher.
        // CommandDispatcher handles *how* to construct the command using what Kernel gives it.
        $commandsRequiringSpecificSpace = ['chat', 'ingest', 'clear'];
        $commandsUsingBaseKnowledgePath = ['interact', 'status'];

        if (in_array($commandName, $commandsRequiringSpecificSpace)) {
            if (empty($spaceOption)) {
                Output::error("Error: Command '{$commandName}' requires a specific cognitive space.");
                Output::error("Please use --space=<path/to/your/space> (e.g., --space=spaces/my_personal_agent).");
                exit(1);
            }
            
            $fullSpacePath = $_SERVER['PWD'] . '/' . $spaceOption;
            // Ensure the provided space path is within the configured spaces_base_path for safety/consistency
            if (!str_starts_with(realpath($fullSpacePath), realpath($spacesBasePath))) {
                 Output::error("Error: The specified knowledge space '{$spaceOption}' is not within the configured knowledge base path '{$spacesBasePath}'.");
                 exit(1);
            }

            try {
                $knowledgeSpace = new Space($fullSpacePath);
            } catch (\InvalidArgumentException $e) {
                Output::error("Error initializing cognitive space '{$fullSpacePath}': " . $e->getMessage());
                exit(1);
            }
            $commandConfig['knowledgeSpace'] = $knowledgeSpace;

        } elseif (in_array($commandName, $commandsUsingBaseKnowledgePath)) {
            $commandConfig['knowledgeBasePath'] = $spacesBasePath;
        }
        // --- End command-specific handling ---
        
        try {
            $exitCode = $commandDispatcher->dispatch($commandName, $commandConfig);
            exit($exitCode);
        } catch (\RuntimeException $e) {
            Output::error("An error occurred during command dispatch: " . $e->getMessage());
            exit(1);
        }
    }
}
