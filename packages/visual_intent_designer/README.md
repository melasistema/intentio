# Visual Intent Designer Package

A specialized cognitive instrument designed for Art Directors and Fashion Photographers to translate abstract creative visions into precise technical render manifests for the Flux2 image generation model. This package moves beyond generic "prompt engineering" by focusing on the language and principles of traditional photography and art direction.

## How It Works

Instead of crafting verbose text prompts, the Visual Intent Designer guides the user through a structured process that leverages a curated knowledge base of photographic physics, lighting principles, material science, and brand aesthetics. The system facilitates the articulation of a "Design Intent," which is then automatically translated into a "Render Manifest" – a highly technical specification understood by the Flux2 model.

This ensures:
*   **Precision:** Technical terms (e.g., focal length, lighting ratios, textile properties) replace vague keywords.
*   **Consistency:** Integration with `house_style.md` ensures brand visual DNA is maintained across all renders.
*   **Validation:** Physics-based constraints can flag impossible or contradictory requests (e.g., "ultra-wide with no distortion").

## Available Commands (Prompts)

*   **`design_intent`**
    *   Guides the user to define the conceptual core, technical photographic specifications (optics, illumination, materials), and high-level artistic goals for a visual.
    *   *Instruction: Describe your raw vision or the 'feeling' of the shot:*

*   **`render_manifest`**
    *   (Internal/System-Generated) Converts a validated Design Intent into a Flux2-compatible technical specification, ready for image generation.
    *   *Instruction: This prompt is not directly user-invoked; it is a system output based on `design_intent` processing.*

## Key Knowledge Bases Integrated:

*   **`camera_atlas.md`**: Maps visual "vibes" to precise focal lengths, apertures, and sensor characteristics.
*   **`lighting_library.md`**: Defines various lighting setups, their technical parameters, and emotional impact.
*   **`textile_dictionary.md`**: Translates material properties (e.g., "Silk Charmeuse") into Flux2-interpretable visual characteristics (sheen, drape, texture).
*   **`house_style.md`**: Enforces brand-specific visual DNA, color grading, compositional rules, and forbidden traits.
*   **`past_campaigns.md`**: Provides historical context and lessons from successful previous visual campaigns.
*   **`aesthetic_rules.md`**: High-level guidelines and "hard stops" for visual integrity.

## Example Usage

1.  Start interactive mode: `./intentio interact`
2.  Select the `visual_intent_designer` space.
3.  Select the `design_intent` template. The system will then prompt you with instructions:

```
Current Knowledge Space: /path/to/packages/visual_intent_designer
Current Prompt Template: design_intent
Enter the raw vision or the 'feeling' of the shot:
INTENTIO > I want a vulnerable, intimate portrait with a soft background, evoking serenity.
```

The system will then process your input, leverage its knowledge bases to derive technical specifications, and generate a `render_manifest` that Flux2 can use to produce an image aligned with your vision.
