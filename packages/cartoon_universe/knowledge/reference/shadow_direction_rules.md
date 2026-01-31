# Shadow Direction Rules: Model Interpretation for Cartoon Universe

This document defines the simplified and consistent rules for shadow casting within the Cartoon Universe. The goal is to maintain a graphic, non-realistic aesthetic where shadows serve to define form in a stylized manner rather than to simulate realistic light physics.

## I. Simplified Light Source

-   **Implied Single Light Source:** All scenes should imply a single, consistent light source. This source is typically positioned from the top-front-right, providing clear, directional shadows without complex light interactions.
    -   **Keywords:** `single implied light source`, `top-front-right lighting`, `consistent light direction`
-   **No Multiple Light Sources:** Avoid any tokens or effects that suggest multiple light sources or complex studio setups.
    -   **Negative Keywords:** `no multiple light sources`, `no complex lighting setup`

## II. Shadow Characteristics

-   **Cel-Shaded Shadows:** Shadows must be hard-edged, flat shapes, consistent with the `shading_model_library.md`. No soft gradients, feathering, or diffusion.
    -   **Keywords:** `hard-edged shadows`, `flat graphic shadows`, `cel-shaded shadow shapes`
    -   **Negative Keywords:** `no soft shadows`, `no graduated shadows`, `no diffused shadows`
-   **Directional Consistency:** All cast shadows and shaded areas on objects must consistently follow the implied light direction.
    -   **Keywords:** `consistent shadow direction`, `uniform shadow fall`
-   **Simplified Form:** Shadows should simplify the form, acting as graphic elements rather than accurate projections of light falloff.
-   **No Ambient Occlusion:** Avoid any subtle shading that suggests ambient occlusion or realistic contact shadows beyond what is explicitly defined by cel shading.
    -   **Negative Keywords:** `no ambient occlusion`, `no soft contact shadows`

## III. Shadow Intensity & Color

-   **Subdued Intensity:** Shadows should be clearly visible but not overly dark or dramatic. They are for form definition, not mood.
-   **Color:** Shadows should typically be a darker, desaturated version of the base color, or a consistent cool grey/blue tint, as defined in `color_palette_atlas.md`.
    -   **Keywords:** `desaturated shadow color`, `cool-toned shadows`, `graphic shadow tint`

## IV. Forbidden Realistic Shadow Traits

-   **Photorealistic Light Falloff:** No realistic inverse-square law light decay, volumetric shadows, or complex self-shadowing.
-   **Subtle Shadow Details:** Avoid intricate shadow details that suggest highly detailed surfaces or complex geometry.
-   **Reflected Light/Fill Light:** No subtle fill light or reflected light within shadow areas.
    -   **Negative Keywords:** `no realistic shadow physics`, `no subtle shadow detail`, `no reflected light in shadows`, `no fill light`

---

**General Shadow Principles for the Image Generation Model (Cartoon Universe):**

-   **Graphic Simplicity:** Shadows are a graphic tool to enhance form in a cartoon style, not to mimic reality.
-   **Strict Consistency:** The direction and nature of shadows must be utterly consistent across all elements in a scene.
-   **Enforce with Style Lock:** These rules are essential for maintaining the flat, stylized look of the universe.