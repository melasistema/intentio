<?php

declare(strict_types=1);

namespace Intentio\Domain\Model;

interface LLMInterface
{
    /**
     * Asks the language model to answer a prompt.
     *
     * @param string $prompt The complete prompt, with the knowledge in scope already in it.
     * @return string The generated response from the LLM.
     */
    public function generate(string $prompt): string;
}

