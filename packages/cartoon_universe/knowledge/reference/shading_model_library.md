# Shading Model Library: Model Interpretation for Cartoon Universe

This library defines the specific shading techniques permitted and enforced within the Cartoon Universe, ensuring a consistent and graphic visual style.

## I. Primary Shading Model: Cel Shading

-   **Definition:** Cel shading (or toon shading) is a non-photorealistic rendering technique designed to make 3D computer graphics appear flat, like a 2D cartoon. It uses distinct, hard-edged areas of color for shadows, without blending or gradients.
-   **Model Interpretation:**
    -   **Hard Edges:** Shadows must have sharp, clearly defined boundaries. No soft transitions or feathering.
    -   **Limited Tones:** Each area typically uses 1-2 distinct tones (base color + shadow color).
    -   **No Gradients:** Absolutely no smooth color transitions or gradients within shaded areas.
    -   **Graphic Shapes:** Shadows should be simplified, graphic shapes that follow the form rather than realistic light falloff.
-   **Keywords:** `cel-shaded`, `toon shading`, `flat shading`, `hard-edged shadows`, `graphic shadows`, `distinct shadow shapes`, `no soft shadows`, `no gradients`

## II. Light Source Rules

-   **Implied Single Light Source:** While not explicitly rendered, the shading should suggest a consistent, usually top-down or top-front, single light source for all elements in a scene.
-   **Shadow Direction:** Shadows should consistently fall in the same general direction, as defined in `shadow_direction_rules.md`.

## III. Highlight Rules

-   **Minimal & Stylized:** Highlights should be simple, graphic shapes (e.g., small circles, ovals, or hard-edged reflections) on shiny surfaces, indicating reflectivity rather than realistic light interaction.
-   **Limited Application:** Highlights are used sparingly and should not create a sense of metallic sheen or wetness beyond what is appropriate for a flat cartoon.
-   **Keywords:** `stylized highlights`, `graphic reflections`, `minimal highlights`

## IV. Forbidden Shading Traits

-   **Photorealistic Lighting:** Any attempt to render realistic light scattering, global illumination, subsurface scattering, or complex atmospheric effects is strictly prohibited.
-   **Soft Shadows & Blending:** Absolutely no soft shadows, ambient occlusion, or smooth color blending.
-   **Volumetric Lighting:** No light rays, volumetric fog, or other effects that create a sense of realistic atmosphere.
-   **Keywords (Negative):** `no soft shadows`, `no gradients`, `no realistic lighting`, `no global illumination`, `no subsurface scattering`, `no volumetric light`, `no complex reflections`, `no light falloff`

---

**General Shading Principles for the Image Generation Model (Cartoon Universe):**

-   **Graphic & Flat:** Prioritize a flat, graphic appearance over any attempt at three-dimensional realism through shading.
-   **Enforce with Style Lock:** `enforce_shading_model: cel_shaded` in `manifest.md` ensures these rules are followed.
-   **Negate Realism:** Actively use negative prompts to fight against any tendency towards realistic shading.