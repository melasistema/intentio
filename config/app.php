<?php

declare(strict_types=1);

return [
    'app_name' => 'INTENTIO',
    'app_version' => '0.2.1',

    // Base paths for cognitive spaces and blueprints (packages)
    'spaces_base_path' => __DIR__ . '/../spaces',
    'blueprints_base_path' => __DIR__ . '/../packages',

    // Ollama API configuration
    'ollama' => [
        'base_url' => 'http://localhost:11434',
        'api_path_embeddings' => '/api/embeddings',
        'api_path_generate' => '/api/generate', // Corresponds to original api_path_chat for stateless generation
        'timeout' => 120, // Default timeout, can be overridden in llm/embedding options
    ],

    // Large Language Model (LLM) configuration
    'llm' => [
        'model_name' => 'llama3.1', // From original interpreter.model_name
        'options' => [
            'temperature' => 0.5, // From original interpreter.options
            'keep_alive' => '15m', // From original interpreter.options
        ],
        'default_prompt_template_name' => 'default', // From original interpreter.default_prompt_template_name
    ],

    // Embedding model configuration
    'embedding' => [
        'model_name' => 'nomic-embed-text', // From original embedding.model_name
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