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
}
