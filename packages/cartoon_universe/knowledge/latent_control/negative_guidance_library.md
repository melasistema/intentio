# Negative Guidance Library for Cartoon Universe

This library provides crucial concepts that *must be avoided* to maintain the consistent "Cartoon Universe" style. Since a separate negative prompt channel is not supported by the current image generation model, these concepts must be *rephrased and intelligently integrated into the single positive prompt*. Use phrases like "without", "avoiding", "non-", "un-", "not depicting", or similar constructions to negate these traits within the main prompt.

## I. Forbidden Realism Traits (Core Negative Conditions)

-   `photorealistic`, `realistic`, `realism`, `hyperrealistic`, `hyper-realistic`, `photograph`, `photo`, `natural`
-   `subtle lighting`, `complex lighting`, `natural light`, `ambient occlusion`, `soft shadows`, `gradual shadows`, `volumetric light`, `light shafts`
-   `fine texture`, `detailed skin texture`, `natural hair`, `fur detail`, `fabric texture`, `material textures`
-   `subtle gradients`, `realistic color palette`, `muted colors`, `desaturated colors`, `faded colors`, `complex color blends`
-   `anatomically correct`, `realistic proportions`, `human proportions`, `animal anatomy`
-   `complex reflections`, `specular highlights (realistic)`, `refractive effects`
-   `depth of field`, `motion blur (realistic)`, `grain`, `noise`, `vignette`, `chromatic aberration`
-   `3D render (realistic)`, `CGI look (realistic)`, `uncanny valley`, `ugly`, `disfigured`, `mutated`
-   `painting`, `drawing`, `sketch`, `illustration (realistic style)`, `fine art`, `concept art` (unless specifically allowed for style consistency)
-   `low quality`, `bad art`, `poorly drawn`, `out of frame`, `blurry`, `distorted`

## II. Undesirable Cartoon Inconsistencies

-   `stylistic drift`, `inconsistent style`, `mixed styles`
-   `undefined outlines`, `fuzzy lines`, `variable line weight (uncontrolled)`
-   `color bleed`, `colors outside lines`
-   `complex shading`, `blending`, `rough brush strokes`
-   `cluttered background`, `distracting elements`
-   `text`, `watermark`, `signature`
-   `artifacts`, `pixelation`

---

**General Negative Prompting Principles for the Image Generation Model (Cartoon Universe):**

-   **Aggressive Suppression:** Be aggressive in negating realism and any elements that break the cartoon illusion.
-   **Constant Enforcement:** These terms form the backbone of the style lock and should always be present in the negative prompt.
-   **Prioritize Negative Space:** Ensure that the negative prompt is sufficiently strong to guide the model away from unwanted outputs.