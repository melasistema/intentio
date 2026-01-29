<?php
// temp_parser.php
function parseManifest(string $content): array
{
    $config = [];
    $lines = explode("\n", $content);
    $inActionsBlock = false;
    $currentActionName = null;

    foreach ($lines as $line) {
        $trimmedLine = rtrim($line);

        if (rtrim($trimmedLine) === 'actions:') {
            $inActionsBlock = true;
            $config['actions'] = [];
            continue;
        }

        if ($inActionsBlock) {
            if (strlen($trimmedLine) > 0 && !str_starts_with($trimmedLine, ' ')) {
                $inActionsBlock = false;
                $currentActionName = null;
            } else {
                if (preg_match('/^  (\w+):$/', $trimmedLine, $matches)) {
                    $currentActionName = $matches[1];
                    $config['actions'][$currentActionName] = [];
                    continue;
                }
                if ($currentActionName && preg_match('/^    (\w+): (.*)$/', $trimmedLine, $matches)) {
                    $key = $matches[1];
                    $value = trim($matches[2]);
                    if ($value === 'null') $value = null;
                    elseif ($value === 'true') $value = true;
                    elseif ($value === 'false') $value = false;
                    $config['actions'][$currentActionName][$key] = $value;
                }
                continue;
            }
        }

        if (str_contains($trimmedLine, ':')) {
            list($key, $value) = explode(':', $trimmedLine, 2);
            if (trim($key) !== 'actions') {
                $config[trim($key)] = trim($value);
            }
        }
    }
    return $config;
}

$manifestContent = <<<EOT
# Visual Intent Designer – Manifest

name: Visual Intent Designer
version: 0.1
domain: photography / art direction / image generation
default_prompt: design_intent

description:
A specialized cognitive instrument for Art Directors and Fashion Photographers to translate abstract creative visions into precise technical render manifests for the Flux2 image generation model, leveraging a knowledge base of photographic physics, lighting principles, material science, and brand aesthetics.

actions:
  render:
    description: "Generate an image from the current manifest."
    template: "render_manifest"
    handler: "image_renderer"
    context_required: "lastGeneratedManifest"
    updates_context: null

  refine:
    description: "Refine the current manifest based on new instructions."
    template: "refine_manifest"
    handler: "manifest_llm_refiner"
    context_required: "lastGeneratedManifest"
    query_required: true
    updates_context: "lastGeneratedManifest"
EOT;

$parsedConfig = parseManifest($manifestContent);
echo json_encode($parsedConfig, JSON_PRETTY_PRINT);
?>
