<?php

declare(strict_types=1);

namespace Intentio\Domain\Model;

interface VisionModelInterface
{
    /**
     * Processes an image and returns a textual interpretation or analysis.
     *
     * @param string $imagePath The path to the image file to process.
     * @param string $prompt An optional prompt or question to guide the vision model's interpretation.
     * @param array $options Additional options for the vision model (e.g., model name, output format, specific LLaVA options).
     * @return string A textual interpretation or analysis of the image.
     * @throws \Intentio\Shared\Exceptions\IntentioException If the image processing fails.
     */
    public function analyzeImage(string $imagePath, string $prompt = '', array $options = []): string;
}
