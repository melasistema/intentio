<?php

declare(strict_types=1);

namespace Intentio\Domain\Model;

interface ImageRendererInterface
{
    /**
     * Renders an image based on the provided prompt and options.
     *
     * @param string $prompt The image generation prompt.
     * @param array $options Additional options for rendering (e.g., model name, output format).
     * @return string The path to the rendered image or a unique identifier.
     */
    public function render(string $prompt, array $options = []): string;
}
