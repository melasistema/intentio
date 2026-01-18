<?php

declare(strict_types=1);

namespace Intentio\Command;

use Intentio\Cli\Input;
use Intentio\Cli\Output;
use Intentio\Knowledge\Space;

/**
 * Handles the 'clear' command, removing the vector store (SQLite database)
 * associated with a specified cognitive space.
 *
 * This effectively "resets" the knowledge for that space, allowing for
 * fresh ingestion.
 */
final class ClearCommand implements CommandInterface
{
    public function __construct(
        private readonly Input $input,
        private readonly array $config,
        private readonly Space $knowledgeSpace // The default knowledge space is passed here
    ) {
    }

    public function execute(): int
    {
        Output::writeln("--- Clearing Cognitive Space ---");

        if (!$this->knowledgeSpace) {
            Output::error("No cognitive space specified. Use --space=<path> to specify which space to clear.");
            return 1;
        }

        $knowledgeSpacePath = $this->knowledgeSpace->getRootPath();
        $vectorStoreDbPath = $this->config['vector_store_db_path'];
        $dbFileName = md5($knowledgeSpacePath) . '.sqlite'; // Derive filename as in SQLiteVectorStore
        $dbFilePath = $vectorStoreDbPath . DIRECTORY_SEPARATOR . $dbFileName;

        Output::writeln(sprintf("Attempting to clear data for space: %s (%s)", $knowledgeSpacePath, $dbFilePath));

        if (!file_exists($dbFilePath)) {
            Output::writeln("No existing data found for this space. Nothing to clear.");
            return 0;
        }

        // Prompt for confirmation
        Output::writeln("WARNING: This will permanently delete the data for this cognitive space.");
        $confirmation = readline("Type 'yes' to confirm deletion: ");

        if (trim(strtolower($confirmation)) !== 'yes') {
            Output::writeln("Deletion cancelled.");
            return 1; // Indicate cancellation
        }

        if (unlink($dbFilePath)) {
            Output::writeln(sprintf("Successfully cleared data for cognitive space '%s'.", $knowledgeSpaceName));
            Output::writeln("You may now re-ingest this space if needed.");
            return 0;
        } else {
            Output::error(sprintf("Failed to delete data file '%s'. Please check permissions.", $dbFilePath));
            return 1;
        }
    }
}
