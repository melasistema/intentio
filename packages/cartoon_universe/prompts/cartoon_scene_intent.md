---
instruction: "Describe the character, object, or scene to be rendered in the consistent 'Cartoon Universe' style. This will be translated into a latent-aware prompt for the image generation model."
---
# 🎨 Latent-Aware Cartoon Render Brief: {{QUERY}}

---
**STRUCTURING THE LATENT-DOMINANT RENDER MANIFEST (CARTOON UNIVERSE):**

### 1. Core Visual Grammar & Scene Setup
*Establishes the primary stylistic anchors for the model.*
-   **Dominant Visual Theme:** `vibrant cartoon art style`, `graphic illustration`, `animated film quality`
-   **Cartoon Universe Style Lock:** `consistent bold clean outlines`, `flat color fills`, `cel-shaded aesthetic`
-   **Background:** (e.g., Simple flat color background, Stylized cartoon environment) - Consult `color_palette_atlas.md` for permissible colors.

### 2. Character/Object & Visual Grammar Amplifiers
*Injects high-impact tokens for specific cartoon traits and enforces visual rules.*
-   **Line Art Enforcement:** Consult `line_art_rules.md`.
    -   (e.g., `uniform medium-heavy line weight`, `crisp clean outlines`, `solid opaque lines`)
-   **Color Palette Enforcement:** Consult `color_palette_atlas.md`.
    -   (e.g., `vibrant flat color fills`, `limited color palette`, `highly saturated cartoon colors`)
-   **Shading Model Enforcement:** Consult `shading_model_library.md`.
    -   (e.g., `hard-edged cel shading`, `distinct shadow shapes`, `graphic highlights`)
-   **Character Proportions Enforcement:** Consult `character_anatomy_ratios.md`.
    -   (e.g., `large head small body ratio`, `simplified limbs`, `expressive facial features`)
-   **Perspective & Depth:** Consult `perspective_flattening_techniques.md`.
    -   (e.g., `orthographic projection`, `flat perspective`, `layered background elements`)
-   **Shadow Direction Enforcement:** Consult `shadow_direction_rules.md`.
    -   (e.g., `single implied light source`, `consistent shadow direction`, `flat graphic shadows`)
-   **Style Token Amplification (Subject Specific):** Consult `style_token_amplifiers.md`.
    -   (e.g., For a penguin: `cute cartoon penguin`, `chubby body`, `waddling stance`)
    -   (e.g., For a pen: `simple cartoon pen`, `clean graphic form`)

### 3. Subject Definition
*The specific character, object, or scene elements, interpreted through the cartoon lens.*
-   **Main Subject:** (e.g., Penguin, Toaster, Human character)
-   **Action/Pose:** (e.g., holding a pen, jumping for joy)
-   **Key Features:** (e.g., wearing a tiny hat, glowing buttons)

### 4. Framing & Composition
*These are secondary to the core style but guide the overall image layout.*
-   **Camera Angle:** (e.g., Eye-level, Slightly above, Close-up) - Prioritize clarity and expression over realism.
-   **Composition:** (e.g., Centered, Rule of thirds, Dynamic diagonal)
-   **Simplicity:** Maintain clear, uncluttered compositions.

### 5. Final Render Prompt for the image generation model
*The LLM should assemble a single, cohesive positive prompt string, integrating all elements directly. This string must be wrapped within `<<<RENDER_PROMPT>>>` and `<<<END_RENDER_PROMPT>>>` tags.
The positive prompt should follow the latent-aware hierarchy (1 -> 2 -> 3 -> 4), append the content from `cartoon_style_enhancers.md`, AND intelligently incorporate rephrased negative concepts from `negative_guidance_library.md` using phrasing like "avoiding", "without", "non-", etc. The `--neg` separator is NOT to be used.*

<<<RENDER_PROMPT>>>
[LLM-generated latent-dominant cartoon render prompt here, intelligently merging positive and rephrased negative concepts]
<<<END_RENDER_PROMPT>>>