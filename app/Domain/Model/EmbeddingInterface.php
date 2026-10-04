<?php

declare(strict_types=1);

namespace Intentio\Domain\Model;

interface EmbeddingInterface
{
    /**
     * Generates an embedding (vector representation) for a given text input.
     *
     * @param string $text The text to embed.
     * @return array The embedding as an array of floats.
     */
    public function embed(string $text): array;

    /**
     * Generates the embeddings of several texts in one request.
     *
     * @param string[] $texts The texts to embed.
     * @return array The embeddings, in the order of the texts.
     */
    public function embedAll(array $texts): array;
}
