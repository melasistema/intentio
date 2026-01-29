# Render Manifest Prompt

This prompt instructs the system to use the configured image_renderer (x/flux2-klein) to generate and save images to renderer_images/
Context: {{lastGeneratedManifest}}
Query: {{query}}

Action: Generate image using image_renderer.model_name and save to renderer_images/