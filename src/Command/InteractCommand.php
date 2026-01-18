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
    ) {
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
