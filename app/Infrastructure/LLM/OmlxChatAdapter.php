<?php

declare(strict_types=1);

namespace Intentio\Infrastructure\LLM;

use Intentio\Domain\Model\LLMInterface;
use Intentio\Infrastructure\Omlx\OmlxClient;
use Intentio\Shared\Exceptions\IntentioException;

final class OmlxChatAdapter implements LLMInterface
{
    /**
     * @param array $options Sampling options sent with every request (e.g., temperature, max_tokens).
     */
    public function __construct(
        private readonly OmlxClient $client,
        private readonly string $model,
        private readonly array $options
    ) {
    }

    public function generate(string $prompt): string
    {
        // Every answer is a conversation of one message: INTENTIO keeps no history between queries
        $response = $this->client->post('/v1/chat/completions', $this->options + [
            'model' => $this->model,
            'messages' => [['role' => 'user', 'content' => $prompt]],
        ]);

        $answer = $response['choices'][0]['message']['content'] ?? null;
        if (!is_string($answer)) {
            throw new IntentioException("The answer of oMLX for model '{$this->model}' has no message content.");
        }

        if (trim($answer) === '') {
            throw new IntentioException("Model '{$this->model}' returned an empty response.");
        }

        return $answer;
    }
}
