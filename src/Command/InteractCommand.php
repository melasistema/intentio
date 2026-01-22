<?php

declare(strict_types=1);

namespace Intentio\Command;

use Intentio\Cli\Input;
use Intentio\Cli\Output;
use Intentio\Knowledge\Space;
use Intentio\Orchestration\Prompt; // Added use statement for Prompt
use Intentio\Command\IngestCommand; // Added use statement for IngestCommand

/**
 * Handles the 'interact' command, launching a guided interactive mode.
 *
 * This mode allows users to select a knowledge space and then converse
 * with the cognitive environment in a guided loop, providing a more
 * intuitive user experience.
 */
final class InteractCommand implements CommandInterface
{
    private const LOGO_ASCII = " 
 ___  ________   _________  _______   ________   _________  ___  ________     
|\  \|\   ___  \|\___   ___\\  ___ \ |\   ___  \|\___   ___\\  \|\   __  \    
\ \  \ \  \\ \  \|___ \  \_\ \   __/|\ \  \\ \  \|___ \  \_\ \  \ \  \|\  \   
 \ \  \ \  \\ \  \   \ \  \ \ \  \_|/_\ \  \\ \  \   \ \  \ \ \  \ \  \\\  \  
  \ \  \ \  \\ \  \   \ \  \ \ \  \_|\ \ \  \\ \  \   \ \  \ \ \  \ \  \\\  \ 
   \ \__\ \__\\ \__\   \ \__\ \ \_______\ \__\\ \__\   \ \__\ \ \__\ \_______\
    \|__|\|__| \|__|    \|__|  \|_______|\|__| \|__|    \|__|  \|__|\|_______|
";

    private ?Space $currentKnowledgeSpace = null;
    private ?string $currentPromptTemplateName = null;

    public function __construct(
        private readonly Input $input,
        private readonly array $config,
        private readonly string $knowledgeBasePath // Changed from Space to string
    ) {
        // Initially, no specific space is selected, or use a default if desired.
        // For interact, it's better to let the user select first.
        // Or, if --space was provided to interact, try to set it.
        $initialSpaceOption = $this->input->getOption('space');
        if (!empty($initialSpaceOption)) {
             try {
                $this->currentKnowledgeSpace = new Space($initialSpaceOption);
            } catch (\InvalidArgumentException $e) {
                Output::error("Failed to initialize space from --space option: " . $e->getMessage());
                $this->currentKnowledgeSpace = null; // Fallback to no space selected
            }
        } else {
            $this->currentKnowledgeSpace = null; // Start with no space selected by default
        }
        

    }

    public function execute(): int
    {
        Output::writeln(self::LOGO_ASCII); // Display logo
        Output::writeln("Starting interactive mode. Type 'exit' to quit, 'space' to change knowledge space, 'template' to change prompt template."); // Updated help message

        // Main interactive loop
        while (true) {
            $this->displayCurrentSpace();
            $this->displayCurrentTemplate(); // Display current template

            $query = readline("INTENTIO > ");
            $query = trim($query);

            if ($query === 'exit') {
                Output::writeln("Exiting interactive mode. Goodbye!");
                break;
            } elseif ($query === 'space') {
                $this->selectKnowledgeSpace();
            } elseif ($query === 'template') { // Handle template command
                $this->selectPromptTemplate();
            } elseif (!empty($query)) {
                $this->chat($query);
            } else {
                Output::writeln("Please type your query, 'space', 'template', or 'exit'."); // Updated help message
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
    
    // New method to display current template
    private function displayCurrentTemplate(): void
    {
        if ($this->currentPromptTemplateName !== null) {
            Output::writeln(sprintf("Current Prompt Template: %s", $this->currentPromptTemplateName));
        } else {
            Output::writeln("No Prompt Template selected.");
        }
    }

    private function selectKnowledgeSpace(): void
    {
        Output::writeln("\n--- Select Knowledge Space ---");
        
        $availableSpaces = Space::getAvailableSpaces($this->knowledgeBasePath); // Use local knowledgeBasePath

        if (empty($availableSpaces)) {
            Output::writeln("No knowledge spaces found in '{$this->knowledgeBasePath}'.");
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
            $selectedSpacePath = $this->knowledgeBasePath . DIRECTORY_SEPARATOR . $selectedSpaceName; // Use local knowledgeBasePath
            try {
                $this->currentKnowledgeSpace = new Space($selectedSpacePath);
                Output::writeln(sprintf("Knowledge space set to: %s", $this->currentKnowledgeSpace->getRootPath()));

                // --- Check Ingestion Status ---
                $vectorStoreDbPath = '.intentio_store';
                $dbFilePath = $vectorStoreDbPath . DIRECTORY_SEPARATOR . md5($this->currentKnowledgeSpace->getRootPath()) . '.sqlite'; // Use MD5 hash for filename

                $ingestionStatus = $this->isIngestionNeeded($this->currentKnowledgeSpace->getRootPath(), $dbFilePath);
                
                if ($ingestionStatus === 'needed') {
                    Output::writeln("\nNOTICE: This cognitive space needs to be ingested or re-ingested (source files are newer).");
                    $confirmIngest = readline("Would you like to ingest it now? (yes/no): ");
                    if (trim(strtolower($confirmIngest)) === 'yes') {
                        // Create a temporary Input object for IngestCommand
                        $tempArgv = [
                            'intentio',
                            'ingest',
                            '--space=' . $this->currentKnowledgeSpace->getRootPath(),
                        ];
                        $ingestInput = new Input($tempArgv);

                        $ingestCommand = new IngestCommand( // Use IngestCommand
                            input: $ingestInput,
                            config: $this->config,
                            knowledgeSpace: $this->currentKnowledgeSpace
                        );
                        $ingestCommand->execute();
                        Output::writeln("Ingestion process completed.");
                    } else {
                        Output::writeln("Ingestion skipped. You may experience outdated or limited responses.");
                    }
                } elseif ($ingestionStatus === 'missing') {
                    Output::writeln("\nNOTICE: This cognitive space does not appear to be ingested.");
                    $confirmIngest = readline("Would you like to ingest it now? (yes/no): ");
                    if (trim(strtolower($confirmIngest)) === 'yes') {
                        // Create a temporary Input object for IngestCommand
                        $tempArgv = [
                            'intentio',
                            'ingest',
                            '--space=' . $this->currentKnowledgeSpace->getRootPath(),
                        ];
                        $ingestInput = new Input($tempArgv);

                        $ingestCommand = new IngestCommand( // Use IngestCommand
                            input: $ingestInput,
                            config: $this->config,
                            knowledgeSpace: $this->currentKnowledgeSpace
                        );
                        $ingestCommand->execute();
                        Output::writeln("Ingestion process completed.");
                    } else {
                        Output::writeln("Ingestion skipped. You may experience limited responses without ingested data.");
                    }
                }
                // --- End Check Ingestion Status ---

            } catch (\InvalidArgumentException $e) {
                Output::error("Failed to select knowledge space: " . $e->getMessage());
            }
        } else {
            Output::writeln("Invalid selection.");
        }
        Output::writeln("----------------------------\n");
    }

    // New method to select prompt template
    private function selectPromptTemplate(): void
    {
        Output::writeln("\n--- Select Prompt Template ---");

        if (!$this->currentKnowledgeSpace) {
            Output::writeln("Please select a knowledge space first to see available templates.");
            return;
        }

        $packageName = basename($this->currentKnowledgeSpace->getRootPath()); // Assuming space name is package name for now
        $availableTemplates = Prompt::getAvailableTemplates($packageName);

        if (empty($availableTemplates)) {
            Output::writeln(sprintf("No prompt templates found for package '%s'.", $packageName));
            Output::writeln("Please create '.md' files in 'packages/{$packageName}/prompts/' to define templates.");
            Output::writeln("  [0] Go back / Cancel");
            $selection = (int)trim(readline("Enter number to select a template: "));
            if ($selection === 0) {
                Output::writeln("Prompt template selection cancelled.");
            } else {
                Output::writeln("Invalid selection.");
            }
            Output::writeln("----------------------------\n");
            return;
        }

        foreach ($availableTemplates as $index => $templateName) {
            Output::writeln(sprintf("  [%d] %s", $index + 1, $templateName));
        }
        Output::writeln("  [0] Go back / Cancel");
        
        $selection = readline("Enter number to select a template: ");
        $selection = (int)trim($selection);

        if ($selection === 0) {
            Output::writeln("Prompt template selection cancelled.");
            return;
        }

        if (isset($availableTemplates[$selection - 1])) {
            $this->currentPromptTemplateName = $availableTemplates[$selection - 1];
            Output::writeln(sprintf("Prompt template set to: %s", $this->currentPromptTemplateName));

            // --- Display instruction for the selected prompt ---
            try {
                // Use the static factory method to get the instruction
                $tempPrompt = Prompt::fromTemplateFile(
                    templateName: $this->currentPromptTemplateName,
                    context: [], // Context not needed for just instruction
                    query: '',   // Query not needed for just instruction
                    packageName: $packageName
                );
                if ($tempPrompt->instruction) {
                    Output::writeln($tempPrompt->instruction);
                }
            } catch (\Throwable $e) {
                // If parsing fails for any reason, we just don't show an instruction.
                // The error will be caught properly when the chat command is run.
            }
            // --- End display instruction ---

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
        if (!$this->currentPromptTemplateName) {
            Output::writeln("Please select a prompt template first before chatting. (Type 'template')");
            return;
        }

        // Create a temporary Input object for ChatCommand
        // This is a bit of a hack but avoids deep refactoring of ChatCommand
        // to not depend on a Kernel-provided Input.
        $tempArgv = [
            'intentio',
            'chat',
            $query,
            '--space=' . $this->currentKnowledgeSpace->getRootPath(),
            '--template=' . $this->currentPromptTemplateName // Pass selected template
        ];
        $chatInput = new Input($tempArgv);

        $chatCommand = new ChatCommand(
            input: $chatInput,
            config: $this->config,
            knowledgeSpace: $this->currentKnowledgeSpace
        );
        $chatCommand->execute();
    }

    /**
     * Checks if ingestion is needed for a given cognitive space.
     *
     * @param string $spacePath The root path of the cognitive space (e.g., knowledge/my_space).
     * @param string $dbFilePath The full path to the SQLite database file for this space.
     * @return string 'needed', 'missing', or 'up-to-date'.
     */
    private function isIngestionNeeded(string $spacePath, string $dbFilePath): string
    {
        if (!file_exists($dbFilePath)) {
            return 'missing';
        }

        $dbLastModified = filemtime($dbFilePath);
        if ($dbLastModified === false) {
            // Should not happen if file_exists is true, but handle defensively
            return 'needed'; // Assume needed if can't get mtime
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($spacePath, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            if ($file->isDir()) {
                continue;
            }
            $fileLastModified = $file->getMTime();
            if ($fileLastModified === false) {
                // If a file's mtime cannot be read, assume ingestion is needed
                return 'needed';
            }
            if ($fileLastModified > $dbLastModified) {
                return 'needed'; // A source file is newer than the database
            }
        }

        return 'up-to-date'; // No source files are newer than the database
    }
}