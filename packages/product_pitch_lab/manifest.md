# Product Pitch Lab – Manifest

name: Product Pitch Lab
version: 0.0.1
domain: business validation / product strategy / visual mockups
default_prompt: validate_idea
recommended_generators:
  - pitch_builder
  - mockup_visualizer

description:
A cognitive instrument for entrepreneurs and product managers to validate, refine, and visualize product ideas. Combines business framework analysis with product visualization capabilities, helping users move from concept to pitch-ready presentation.

actions:
  visualize_product:
    description: "Generate a product mockup from the description."
    template: "visualize_product"
    handler: "image_renderer"
    context_required: "lastGeneratedManifest"
    updates_context: "lastGeneratedManifest"
  logo_concept:
    description: "Generate a logo concept from the brand description."
    template: "logo_concept"
    handler: "image_renderer"
    context_required: "lastGeneratedManifest"
    updates_context: "lastGeneratedManifest"
