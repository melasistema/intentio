<?php

declare(strict_types=1);

namespace Intentio\Infrastructure\ImageRenderer;

use Intentio\Domain\Model\ImageRendererInterface;
use Intentio\Shared\Exceptions\IntentioException;

final class OllamaImageRenderer implements ImageRendererInterface
{
    private string $imageRendererModel;
    private string $rendererFolder;

    public function __construct(array $imageRendererConfig)
    {
        $this->imageRendererModel = $imageRendererConfig['model_name'] ?? '';
        $this->rendererFolder = $imageRendererConfig['renderer_folder'] ?? '';
    }

    public function render(string $prompt, array $options = []): string
    {
        fwrite(STDOUT, "DEBUG: OllamaImageRenderer->render received prompt: '" . $prompt . "'" . PHP_EOL);

        $promptArg = escapeshellarg($prompt);
        $modelArg = escapeshellarg($this->imageRendererModel);

        $command = "ollama run {$modelArg} {$promptArg} 2>&1";

        fwrite(STDOUT, "Executing image generation command: {$command}" . PHP_EOL);

        $output = shell_exec($command);

        if ($output === null) {
            throw new IntentioException("Failed to execute Ollama CLI command. Check if 'ollama' is in your system's PATH.");
        }
        
        // Parse the output to find the "Image saved to: " line
        if (preg_match('/Image saved to: (.+)/', $output, $matches)) {
            $ollamaSavedPath = trim($matches[1]);
            
            // Ensure the renderer folder exists
            if (!is_dir($this->rendererFolder)) {
                if (!mkdir($this->rendererFolder, 0777, true)) {
                    throw new IntentioException("Failed to create renderer folder: '{$this->rendererFolder}'.");
                }
            }

            // Generate a unique filename for our application
            $filename = 'render_' . time() . '_' . basename($ollamaSavedPath);
            $targetPath = rtrim($this->rendererFolder, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;

            // Move the file to our target renderer folder
            if (copy($ollamaSavedPath, $targetPath)) {
                // Optionally remove the original file saved by Ollama
                @unlink($ollamaSavedPath); 
                fwrite(STDOUT, "Image rendered and saved to: {$targetPath}" . PHP_EOL);
                return $targetPath; // Return the path to the saved image
            } else {
                throw new IntentioException("Failed to copy rendered image from '{$ollamaSavedPath}' to '{$targetPath}'. Check folder permissions.");
            }
        } else {
            throw new IntentioException("Ollama image generation failed or did not return an image save path. Output: " . $output);
        }
    }
}
