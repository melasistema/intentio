<?php

declare(strict_types=1);

return [
    'app_name' => 'INTENTIO',
    'app_version' => '0.3.0',

    // Base paths for cognitive spaces and blueprints (packages)
    'spaces_base_path' => __DIR__ . '/../spaces',
    'blueprints_base_path' => __DIR__ . '/../packages',

    // oMLX: the local server that runs the language model and the embedding model
    'omlx' => [
        'base_url' => 'http://localhost:8000',
        'api_key' => '', // Set it in config/app.local.php, which is not tracked
        'timeout' => 300, // Seconds to wait while the server sends nothing
    ],

    // Large Language Model (LLM) configuration
    'llm' => [
        'model_name' => 'Mistral-7B-Instruct-v0.3-4bit', // The model's name as oMLX lists it
        // How many tokens the model can read at once. It is a property of the model and of oMLX's settings;
        // INTENTIO only uses it to warn when a prompt is estimated to be larger.
        'context_window' => 32768,
        'options' => [
            'temperature' => 0.5,
        ],
        'default_prompt_template_name' => 'default',
    ],

    // Embedding model configuration
    'embedding' => [
        'model_name' => 'nomicai-modernbert-embed-base-bf16', // The model's name as oMLX lists it
        // Some embedding models expect a word in front of each text that says what it is. Nomic's models do:
        // without these, unrelated passages score almost as high as related ones. Leave both empty for a model that has none.
        'document_prefix' => 'search_document: ',
        'query_prefix' => 'search_query: ',
    ],

    // Retrieval configuration
    'retrieval' => [
        'limit' => 5, // The most passages a query may bring into the prompt
        // The lowest similarity (0 to 1) a passage needs to be used. It depends on the embedding model.
        // With the default model, unrelated questions scored up to 0.29 and related ones from 0.35.
        'min_score' => 0.32,
    ],

    // Image renderer configuration: mflux, a command line tool that runs image models locally
    'image_renderer' => [
        // The mflux command of the model's family. Give its full path if it is not on your PATH.
        'command' => 'mflux-generate-flux2',
        // The command used when a prompt template renders from kept images ('uses' in its front matter)
        'edit_command' => 'mflux-generate-flux2-edit',
        'model_name' => 'mflux-community/flux2-klein-4b-mflux-q4', // A repository mflux downloads on the first render (about 4.5 GB), or the path of a model folder
        // Passed to the command as --name value
        'options' => [
            'steps' => 4,
            'width' => 1024,
            'height' => 1024,
        ],
    ],

    // Vision model configuration
    'vision_model' => [
        'model_name' => 'llava:34b',
        'options' => [
            'temperature' => 0.4, // A slightly lower temperature for more factual interpretation
            'num_predict' => 1024, // Example: how many tokens to predict
        ],
    ],
];