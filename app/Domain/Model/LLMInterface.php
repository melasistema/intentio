<?php

declare(strict_types=1);

namespace Intentio\Domain\Model;

interface LLMInterface
{
    /**
     * Interacts with the Large Language Model to generate responses based on a given prompt and context.
     *
     * @param string $prompt The primary prompt for the LLM.
     * @param string $context Additional context or retrieved information for the LLM.
     * @param array $options Model-specific options (e.g., temperature, max_tokens).
     * @return string The generated response from the LLM.
     */
    public function generate(string $prompt, string $context = '', array $options = []): string;
}
