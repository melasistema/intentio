# Line Art Rules: Model Interpretation for Cartoon Universe

This document defines the strict rules for line art rendering within the Cartoon Universe, ensuring consistent visual style across all generated elements.

## I. Line Thickness Rules

-   **Uniform Weight:** All primary outlines for characters, objects, and major environmental elements must adhere to a consistent, medium-heavy line weight.
    -   **Keywords:** `uniform line weight`, `consistent thick outline`, `bold clean lines`
-   **No Tapering/Variable Width:** Lines should not vary in thickness based on pressure or form, maintaining a graphic, non-realistic quality.
    -   **Negative Keywords:** `no variable line weight`, `no delicate tapering`, `no thin lines`
-   **Inner Details:** Internal lines for details (e.g., facial features, clothing folds) should be slightly thinner but still maintain a consistent weight, and clearly defined.
    -   **Keywords:** `crisp inner lines`, `defined internal details`

## II. Line Quality

-   **Clean & Crisp:** Lines must be perfectly smooth, sharp, and free from any fuzziness, anti-aliasing artifacts that suggest realism, or unintended breaks.
    -   **Keywords:** `sharp vector lines`, `clean crisp edges`, `smooth outlines`
    -   **Negative Keywords:** `no fuzzy lines`, `no aliased edges`, `no textured lines`
-   **Solid & Opaque:** Lines should be fully opaque, solid black (or defined outline color), without transparency or brush texture.
    -   **Keywords:** `solid black outline`, `fully opaque lines`
    -   **Negative Keywords:** `no transparent lines`, `no brush texture`, `no sketched lines`

## III. Outline Logic

-   **Defined Silhouettes:** Every distinct form should have a clear, unbroken outline, separating it cleanly from other elements and the background.
    -   **Keywords:** `strong silhouette outlines`, `clear form definition`
-   **No Contour Variation for Shading:** Line art should solely define form, not attempt to convey shading or volume through varying line density or cross-hatching.
    -   **Negative Keywords:** `no cross-hatching`, `no line shading`, `no contour lines for volume`
-   **Overlap & Depth:** Overlapping elements should have their outlines clearly rendered, indicating hierarchy and simple depth.

---

**General Line Art Principles for the Image Generation Model (Cartoon Universe):**

-   **Graphic Simplicity:** Prioritize clear, graphic representation over intricate realism.
-   **Non-Photorealistic:** Actively negate any tokens that suggest photographic or traditional art line qualities.
-   **Enforce with Style Lock:** These rules are fundamental and must be strictly enforced via `style_lock_mode` in the manifest.