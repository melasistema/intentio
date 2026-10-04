<?php

declare(strict_types=1);

return [
    'app_name' => 'INTENTIO',
    'app_version' => '0.2.1',

    // Base paths for cognitive spaces and blueprints (packages)
    'spaces_base_path' => __DIR__ . '/../spaces',
    'blueprints_base_path' => __DIR__ . '/../packages',

    // oMLX: the local server that runs the language model and the embedding model
    'omlx' => [
        'base_url' => 'http://localhost:8000',
        'api_key' => '', // Set it in config/app.local.php, which is not tracked
        'timeout' => 300, // Seconds to wait for an answer
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
    ],

    // Retrieval configuration
    'retrieval' => [
        'limit' => 5, // The most passages a query may bring into the prompt
        // The lowest similarity (0 to 1) a passage needs to be used. It depends on the embedding model:
        // the default was measured with nomic-embed-text and has to be checked again for another model.
        'min_score' => 0.5,
    ],

    // Image renderer configuration
    'image_renderer' => [
        'model_name' => 'x/z-image-turbo',
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