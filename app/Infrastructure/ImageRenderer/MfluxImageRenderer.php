<?php

declare(strict_types=1);

namespace Intentio\Infrastructure\ImageRenderer;

use Intentio\Domain\Model\ImageRendererInterface;
use Intentio\Shared\Exceptions\IntentioException;

/**
 * Renders images with mflux, a command line tool that runs image models locally.
 */
final class MfluxImageRenderer implements ImageRendererInterface
{
    private string $command;
    private string $model;
    private array $options;

    public function __construct(array $imageRendererConfig)
    {
        $this->command = $imageRendererConfig['command'] ?? '';
        $this->model = $imageRendererConfig['model_name'] ?? '';
        $this->options = $imageRendererConfig['options'] ?? [];
    }

    public function render(string $prompt, string $rendererFolder): string
    {
        if ($this->command === '' || $this->model === '') {
            throw new IntentioException("Image rendering is not configured: set image_renderer.command and image_renderer.model_name.");
        }

        if (!is_dir($rendererFolder) && !mkdir($rendererFolder, 0777, true)) {
            throw new IntentioException("Failed to create renderer folder: '{$rendererFolder}'.");
        }

        $targetPath = rtrim($rendererFolder, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'render_' . $this->localTime()->format('Ymd-His') . '.png';

        $command = escapeshellarg($this->command)
            . ' --model ' . escapeshellarg($this->model)
            . ' --prompt ' . escapeshellarg($prompt)
            . ' --output ' . escapeshellarg($targetPath);
        foreach ($this->options as $name => $value) {
            $command .= ' --' . $name . ' ' . escapeshellarg((string) $value);
        }

        // mflux writes its progress to the terminal itself: a render takes a while, and the wait should be visible
        passthru($command, $exitCode);

        if ($exitCode === 127) {
            throw new IntentioException(
                "The command '{$this->command}' was not found. Install mflux, or set image_renderer.command to the full path of the command."
            );
        }

        if ($exitCode !== 0 || !file_exists($targetPath)) {
            throw new IntentioException("Image rendering with '{$this->command}' failed (exit code {$exitCode}). Its own messages are above.");
        }

        fwrite(STDOUT, "Image rendered and saved to: {$targetPath}" . PHP_EOL);

        return $targetPath;
    }

    /**
     * The current time on this machine's clock. PHP keeps its own timezone setting, which is often UTC,
     * so the system's timezone is read from where macOS and Linux keep it.
     */
    private function localTime(): \DateTimeImmutable
    {
        $zoneFile = @readlink('/etc/localtime');
        if ($zoneFile === false || !str_contains($zoneFile, '/zoneinfo/')) {
            return new \DateTimeImmutable();
        }

        // The link ends in the timezone's name, e.g. /var/db/timezone/zoneinfo/Europe/Rome
        $zoneName = substr($zoneFile, strpos($zoneFile, '/zoneinfo/') + strlen('/zoneinfo/'));
        if (!in_array($zoneName, timezone_identifiers_list(), true)) {
            return new \DateTimeImmutable();
        }

        return new \DateTimeImmutable('now', new \DateTimeZone($zoneName));
    }
}
