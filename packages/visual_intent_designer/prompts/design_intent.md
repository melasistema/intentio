---
instruction: "Describe the raw creative vision for the shot. Focus on mood, subject, lighting, and environment."
---
# 👁️ Creative Vision Brief: {{QUERY}}

---
**DRAFT TECHNICAL RENDER MANIFEST (FOR REVIEW):**

### 1. Conceptual Core
Define the narrative. What is the story of this single frame?
*Context:* [Analysis of Creative Vision against `past_campaigns.md` and `aesthetic_rules.md` to identify themes, mood, and brand alignment.]

### 2. Director’s Frame (Technical Specs)
Translate the Conceptual Core into technical photographic specifications.
- **Optics:** Consult `camera_atlas.md` for appropriate Focal length, Aperture, and Sensor/Film Stock.
- **Illumination:** Consult `lighting_library.md` for Light quality, Ratio, and Color Temperature.
- **CMF:** Consult `textile_dictionary.md` for Material (Color, Material, Finish) interpretation for key elements.
- **Aesthetics:** Consult `house_style.md` and `aesthetic_rules.md` for Color Palette, Contrast, Composition, and Forbidden Visual Traits.

### 3. Final Image Render Prompt (x/flux2-klein)
This is the precise, final prompt for the `x/flux2-klein` image generation model, directly usable without further interpretation.
Focus on: **Subject -> Action -> Environment -> Lighting -> Technicals.**
Output ONLY the prompt string, wrapped in `<<<RENDER_PROMPT>>>` and `<<<END_RENDER_PROMPT>>>` tags.

<<<RENDER_PROMPT>>>
[LLM-generated image render prompt here]
<<<END_RENDER_PROMPT>>>