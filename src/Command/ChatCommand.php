<?php

declare(strict_types=1);

namespace Intentio\Command;

use Intentio\Cli\Input;
use Intentio\Cli\Output;
use Intentio\Knowledge\Space;
use Intentio\Embedding\NomicEmbedder;
use Intentio\Storage\SQLiteVectorStore; // Updated use statement
use Intentio\Orchestration\Prompt;
use Intentio\Orchestration\LlamaInterpreter;

/**
 * Handles the 'chat' command, allowing interaction with a cognitive environment.
 *
 * This class orchestrates the steps involved in a chat interaction:
 * retrieving the query, processing the knowledge space, performing retrieval,
 * and interpreting the context to generate a response.
 */
final class ChatCommand implements CommandInterface
{
    public function __construct(
        private readonly Input $input,
        private readonly array $config,
        private readonly ?Space $knowledgeSpace = null
    ) {
    }

    public function execute(): int
    {
        Output::writeln("Initiating chat with the cognitive environment...");

        $query = $this->input->getArgument(0); // Assuming the query is the first argument after 'chat'
        if (!$query) {
            Output::writeln("Please provide a query for the chat command.");
            return 1; // Indicate error
        }

        Output::writeln("Your query: \"{$query}\"");

        if (!$this->knowledgeSpace) {
            Output::writeln("No specific knowledge space provided. Operating without context.");
            return 1; // Exit if no space is provided for chat
        }

        Output::writeln("Using knowledge space: " . $this->knowledgeSpace->getRootPath());
        $templateName = $this->input->getOption('template') ?? $this->config['interpreter']['default_prompt_template_name']; // Moved definition
        Output::writeln(sprintf("Using prompt template: %s", $templateName));

        // --- Core Chat Logic ---

        // 1. Load Vector Store
        $vectorStore = new SQLiteVectorStore($this->knowledgeSpace->getRootPath(), '.intentio_store');

        // 2. Embed the user query
        Output::writeln("Embedding your query...");
        $embedder = new NomicEmbedder(
            $this->config['embedding']['model_name'],
            $this->config['ollama']
        );
        $queryEmbedding = $embedder->embed($query);

        // 3. Retrieve relevant context from the Vector Store
        Output::writeln("Retrieving relevant context...");
        $retrievedChunks = $vectorStore->findSimilar($queryEmbedding, 3); // Get top 3 chunks

        $context = [];
        if (!empty($retrievedChunks)) {
            Output::writeln("Found relevant context!");
            
            // --- DEBUGGING OUTPUT (Removed for final code) ---
            // Output::writeln("\n--- Retrieved Chunks (for debugging) ---");
            // foreach ($retrievedChunks as $chunk) {
            //     Output::writeln("Score: " . round($chunk['score'], 4));
            //     Output::writeln("Content: " . substr($chunk['content'], 0, 100) . "...");
            //     Output::writeln("Source: " . $chunk['metadata']['filename'] . " (Category: " . $chunk['metadata']['category'] . ")");
            //     Output::writeln("---");
            // }
            // Output::writeln("---------------------------------------\n");
            // --- END DEBUGGING OUTPUT ---

            foreach ($retrievedChunks as $chunk) {
                $context[] = $chunk['content'];
            }
        } else {
            Output::writeln("No relevant context found in this space.");
            $context[] = "No specific information found in the knowledge base related to the query.";
        }

        // 4. Construct the prompt for the Interpreter (LLM)
        // $templateName is already defined above

        $promptBuilder = new Prompt(
            templateName: $templateName,
            globalPromptTemplatesPath: $this->config['prompt_templates_path'], // Use the renamed config key
            context: $context,
            query: $query,
            packageName: $this->config['active_package'] ?? null // Pass the active package name
        );
        $finalPrompt = $promptBuilder->build();

        // 5. Interact with the Interpreter (LLM)
        Output::writeln("Sending prompt to interpreter...");
        $interpreter = new LlamaInterpreter(
            $this->config['interpreter']['model_name'],
            $this->config['ollama'],
            $this->config['interpreter']['options'] ?? []
        );
        $response = $interpreter->interpret($finalPrompt);

        // 6. Display the response
        Output::writeln("\n--- Interpreter Response ---");
        Output::writeln($response);
        Output::writeln("----------------------------\n");

        return 0; // Indicate success
    }
}
