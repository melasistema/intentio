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

    public function generate(string $prompt, ?callable $onText = null): string
    {
        $answer = '';

        // Every answer is a conversation of one message: INTENTIO keeps no history between queries
        $request = $this->options + [
            'model' => $this->model,
            'messages' => [['role' => 'user', 'content' => $prompt]],
            'stream' => true,
        ];

        $this->client->stream('/v1/chat/completions', $request, function (array $event) use (&$answer, $onText): void {
            $text = $event['choices'][0]['delta']['content'] ?? '';
            if (!is_string($text) || $text === '') {
                return; // The first and last events carry no text
            }

            $answer .= $text;
            if ($onText !== null) {
                $onText($text);
            }
        });

        if (trim($answer) === '') {
            throw new IntentioException("Model '{$this->model}' returned an empty response.");
        }

        return $answer;
    }
}
