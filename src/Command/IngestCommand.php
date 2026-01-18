<?php

declare(strict_types=1);

namespace Intentio\Command;

use Intentio\Cli\Input;
use Intentio\Cli\Output;
use Intentio\Knowledge\Space;
use Intentio\Embedding\NomicEmbedder;
use Intentio\Storage\SQLiteVectorStore; // Updated use statement
use Intentio\Ingestion\FileProcessor;

/**
 * Handles the 'ingest' command, processing files within a knowledge space.
 *
 * This command scans the specified knowledge space, processes each file
 * to extract content and metadata, generates embeddings for the content,
 * and stores them in a vector store for later retrieval.
 */
final class IngestCommand
{
    public function __construct(
        private readonly Input $input,
        private readonly array $config,
        private readonly Space $knowledgeSpace
    ) {
    }

    public function execute(): int
    {
        Output::writeln("Initiating ingestion of knowledge space: " . $this->knowledgeSpace->getRootPath());

        // 1. Scan the knowledge space for files
        $files = $this->knowledgeSpace->scan();
        if (empty($files)) {
            Output::writeln("No files found in the knowledge space to ingest.");
            return 0;
        }

        Output::writeln(sprintf("Found %d files to process.", count($files)));

        // 2. Prepare components for ingestion
        $fileProcessor = new FileProcessor();
        $embedder = new NomicEmbedder(
            $this->config['embedding']['model_name'],
            $this->config['ollama']
        );
        
        $vectorStoreDbPath = $this->config['vector_store_db_path']; // Updated config key
        $knowledgeSpaceName = basename($this->knowledgeSpace->getRootPath());
        $vectorStore = new SQLiteVectorStore($knowledgeSpaceName, $vectorStoreDbPath); // Updated class name

        // 3. Process each file
        foreach ($files as $asset) {
            $filePath = $asset['path'];
            $category = $asset['category'];

            Output::writeln(sprintf("Processing file: %s (Category: %s)", basename($filePath), $category));
            
            try {
                // Read and chunk content
                $chunks = $fileProcessor->process($filePath, $category);
                
                foreach ($chunks as $chunk) {
                    Output::writeln(sprintf("  - Embedding chunk (length: %d)...", $chunk['metadata']['chunk_length']));
                    $embedding = $embedder->embed($chunk['content']);
                    $vectorStore->add($chunk, $embedding);
                }
            } catch (\Throwable $e) {
                Output::error("Error processing file {$filePath}: " . $e->getMessage());
                // Continue to next file
            }
        }

        // 4. Save the vector store - No longer needed with SQLite
        // $vectorStore->save();
        Output::writeln("Ingestion complete. Vector store updated (SQLite)."); // Updated message

        return 0; // Indicate success
    }
}
