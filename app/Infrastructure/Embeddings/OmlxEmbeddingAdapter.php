<?php

declare(strict_types=1);

namespace Intentio\Infrastructure\Embeddings;

use Intentio\Domain\Model\EmbeddingInterface;
use Intentio\Infrastructure\Omlx\OmlxClient;
use Intentio\Shared\Exceptions\IntentioException;

final class OmlxEmbeddingAdapter implements EmbeddingInterface
{
    public function __construct(
        private readonly OmlxClient $client,
        private readonly string $model
    ) {
    }

    public function embed(string $text): array
    {
        return $this->embedAll([$text])[0];
    }

    public function embedAll(array $texts): array
    {
        if (empty($texts)) {
            return [];
        }

        $response = $this->client->post('/v1/embeddings', [
            'model' => $this->model,
            'input' => array_values($texts),
        ]);

        // Each result carries the position of its text, so the order of the answer is not relied on
        $embeddings = [];
        foreach ($response['data'] ?? [] as $item) {
            if (isset($item['index'], $item['embedding']) && is_array($item['embedding'])) {
                $embeddings[$item['index']] = $item['embedding'];
            }
        }
        ksort($embeddings);

        if (count($embeddings) !== count($texts)) {
            throw new IntentioException(sprintf(
                "oMLX returned %d embeddings for %d texts with model '%s'.",
                count($embeddings),
                count($texts),
                $this->model
            ));
        }

        return array_values($embeddings);
    }
}
