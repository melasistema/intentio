<?php

// Copy this file to `config.php` and modify its values.
// `config.php` is ignored by Git and will not be committed.

return array (
  'app_name' => 'INTENTIO',
  'spaces_base_path' => 'spaces', // The base directory where all cognitive spaces are stored.
  'ollama' => 
  array (
    'base_url' => 'http://localhost:11434',
    'api_path_embeddings' => '/api/embeddings',
    'api_path_chat' => '/api/chat',
  ),
  'interpreter' => 
  array (
    'model_name' => 'llama3.1',
    'default_prompt_template_name' => 'default', // The default template to use when none is specified.
    'options' => 
    array (
      'temperature' => 0.5,
      'keep_alive' => '15m',
    ),
  ),
  'embedding' => 
  array (
    'model_name' => 'nomic-embed-text', // The Ollama model to use for generating embeddings.
  ),
);