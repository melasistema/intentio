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