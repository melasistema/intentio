<?php

declare(strict_types=1);

namespace Intentio\Domain\Model;

interface ImageRendererInterface
{
    /**
     * Renders an image from a prompt.
     *
     * @param string $prompt The image generation prompt.
     * @param string $rendererFolder The folder the image is saved in.
     * @param string[] $referenceImages Paths of images the model is shown, in the order the prompt refers to them.
     * @return string The path to the rendered image.
     */
    public function render(string $prompt, string $rendererFolder, array $referenceImages = []): string;
}
