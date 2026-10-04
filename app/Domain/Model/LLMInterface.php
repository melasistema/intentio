<?php

declare(strict_types=1);

namespace Intentio\Domain\Model;

interface LLMInterface
{
    /**
     * Asks the language model to answer a prompt.
     *
     * @param string $prompt The complete prompt, with the knowledge in scope already in it.
     * @param callable|null $onText Called with each piece of the answer as the model writes it, so it can be shown without waiting for the end.
     * @return string The complete answer.
     */
    public function generate(string $prompt, ?callable $onText = null): string;
}

