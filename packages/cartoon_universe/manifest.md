# Cartoon Universe – Manifest

name: Cartoon Universe
version: 0.1
domain: cartoon style / visual grammar / consistent aesthetics
default_prompt: cartoon_scene_intent

description:
A specialized cognitive instrument designed to generate consistent, high-quality images adhering to a predefined cartoon aesthetic. It enforces a robust visual grammar across diverse subjects, preventing stylistic drift and ensuring all outputs belong to the same "Cartoon Universe." This environment acts as a style engine, making the generation of cartoon assets effortless and visually cohesive.

style_lock_mode:
locked_visual_signature: true
enforce_line_art_rules: strict
enforce_color_palette: "universe_default"
enforce_shading_model: "cel_shaded"
forbid_realism_traits: true

actions:
  render:
    description: "Generate an image in the cartoon universe style from the current manifest."
    template: "render_cartoon_manifest"
    handler: "image_renderer"
    context_required: "lastGeneratedManifest"
    updates_context: null

  refine:
    description: "Refine the current cartoon manifest based on new instructions."
    template: "refine_cartoon_manifest"
    handler: "manifest_llm_refiner"
    context_required: "lastGeneratedManifest"
    query_required: true
    updates_context: "lastGeneratedManifest"