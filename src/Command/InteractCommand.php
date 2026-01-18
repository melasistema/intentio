<?php

declare(strict_types=1);

namespace Intentio\Command;

use Intentio\Cli\Input;
use Intentio\Cli\Output;
use Intentio\Knowledge\Space;

/**
 * Handles the 'interact' command, launching a guided interactive mode.
 *
 * This mode allows users to select a knowledge space and then converse
 * with the cognitive environment in a guided loop, providing a more
 * intuitive user experience.
 */
final class InteractCommand
{
    private ?Space $currentKnowledgeSpace = null;

    public function __construct(
        private readonly Input $input,
        private readonly array $config,
        private readonly Space $knowledgeSpace
    )
    {
        $this->currentKnowledgeSpace = $knowledgeSpace;
    }

    public function execute(): int
    {
        Output::writeln("Starting interactive mode. Type 'exit' to quit, 'space' to change knowledge space.");

        // Main interactive loop
        while (true) {
            $this->displayCurrentSpace();

            $query = readline("INTENTIO > ");
            $query = trim($query);

            if ($query === 'exit') {
                Output::writeln("Exiting interactive mode. Goodbye!");
                break;
            } elseif ($query === 'space') {
                $this->selectKnowledgeSpace();
            } elseif (!empty($query)) {
                $this->chat($query);
            } else {
                Output::writeln("Please type your query, 'space', or 'exit'.");
            }
        }

        return 0;
    }

    private function displayCurrentSpace(): void
    {
        if ($this->currentKnowledgeSpace) {
            Output::writeln(sprintf("Current Knowledge Space: %s", $this->currentKnowledgeSpace->getRootPath()));
        } else {
            Output::writeln("No Knowledge Space selected.");
        }
    }

    private function selectKnowledgeSpace(): void
    {
        Output::writeln("\n--- Select Knowledge Space ---");
        
        $knowledgeBasePath = $this->config['knowledge_base_path'];
        $availableSpaces = Space::getAvailableSpaces($knowledgeBasePath);

        if (empty($availableSpaces)) {
            Output::writeln("No knowledge spaces found in '{$knowledgeBasePath}'.");
            Output::writeln("Please create subdirectories in this path to define knowledge spaces.");
            return;
        }

        foreach ($availableSpaces as $index => $spaceName) {
            Output::writeln(sprintf("  [%d] %s", $index + 1, $spaceName));
        }
        Output::writeln("  [0] Go back / Cancel");

        $selection = readline("Enter number to select a space: ");
        $selection = (int)trim($selection);

        if ($selection === 0) {
            Output::writeln("Knowledge space selection cancelled.");
            return;
        }

        if (isset($availableSpaces[$selection - 1])) {
            $selectedSpaceName = $availableSpaces[$selection - 1];
            $selectedSpacePath = $knowledgeBasePath . DIRECTORY_SEPARATOR . $selectedSpaceName;
            try {
                $this->currentKnowledgeSpace = new Space($selectedSpacePath);
                Output::writeln(sprintf("Knowledge space set to: %s", $this->currentKnowledgeSpace->getRootPath()));
            } catch (\InvalidArgumentException $e) {
                Output::error("Failed to select knowledge space: " . $e->getMessage());
            }
        } else {
            Output::writeln("Invalid selection.");
        }
        Output::writeln("----------------------------\n");
    }

    private function chat(string $query): void
    {
        if (!$this->currentKnowledgeSpace) {
            Output::writeln("Please select a knowledge space first before chatting.");
            return;
        }

        // Create a temporary Input object for ChatCommand
        // This is a bit of a hack but avoids deep refactoring of ChatCommand
        // to not depend on a Kernel-provided Input.
        $tempArgv = ['intentio', 'chat', $query, '--space=' . $this->currentKnowledgeSpace->getRootPath()];
        $chatInput = new Input($tempArgv);

        $chatCommand = new ChatCommand(
            input: $chatInput,
            config: $this->config,
            knowledgeSpace: $this->currentKnowledgeSpace
        );
        $chatCommand->execute();
    }
}
