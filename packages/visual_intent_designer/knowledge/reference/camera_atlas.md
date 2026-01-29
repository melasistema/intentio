# Camera Atlas: Mapping Vibe to Physics for Flux2

This atlas translates desired visual "vibes" into precise photographic technical specifications. Flux2 should interpret these parameters to simulate realistic camera behavior.

## I. Focal Length & Perspective

-   **20mm - 24mm (Ultra-Wide):**
    -   **Vibe:** Expansive, immersive, dramatic, sometimes distorted (especially at edges). Sense of smallness or grandeur.
    -   **Flux2 Interpretation:** Exaggerated perspective, deep depth of field (if aperture allows), potential for barrel distortion, suitable for vast landscapes, architectural interiors, or dynamic action shots where context is paramount. Subjects near the lens will appear larger.
    -   **Use Case:** "Overwhelmed by nature," "architectural grandeur," "dynamic full-body action."

-   **35mm - 50mm (Standard/Natural):**
    -   **Vibe:** Documentary, natural, human-eye perspective, immersive but not distorted. Balanced relationship between subject and environment.
    -   **Flux2 Interpretation:** Minimal perspective distortion, natural field of view. Versatile for street photography, environmental portraits, general scenes. Renders as "what the eye sees."
    -   **Use Case:** "Authentic moment," "slice of life," "intimate setting where environment matters."

-   **85mm - 135mm (Short Telephoto / Portrait):**
    -   **Vibe:** Intimate, isolating, dream-like, selective focus. Subject emphasis.
    -   **Flux2 Interpretation:** Significant background compression (flattening), beautiful bokeh (especially at wide apertures), very shallow depth of field. Ideal for portraits where the subject is isolated from a busy background.
    -   **Use Case:** "Vulnerable portrait," "focused on expression," "dreamy isolation."

-   **200mm+ (Long Telephoto):**
    -   **Vibe:** Distant, observational, highly compressed. Abstract background.
    -   **Flux2 Interpretation:** Extreme background compression, extremely shallow depth of field, often used for wildlife, sports, or abstracting distant elements. Can create a sense of voyeurism or detachment.
    -   **Use Case:** "Observational study," "abstracted urban landscape," "intense focus on a small detail."

## II. Aperture & Depth of Field (DoF)

-   **f/1.2 - f/2.8 (Very Wide Aperture):**
    -   **Vibe:** Extremely shallow DoF, creamy bokeh, high subject isolation, often romantic or ethereal.
    -   **Flux2 Interpretation:** Pronounced foreground/background blur, smooth out-of-focus areas (bokeh), often used for single subjects. Higher potential for chromatic aberration at edges (if simulated).
    -   **Use Case:** "Intimate portrait," "dreamy product shot," "artistic blur."

-   **f/4 - f/8 (Medium Aperture):**
    -   **Vibe:** Balanced DoF, clear subject with discernible background, journalistic.
    -   **Flux2 Interpretation:** Moderate depth of field, allowing both subject and immediate surroundings to be in focus. Good for environmental portraits or small group shots.
    -   **Use Case:** "Environmental portrait," "small group interaction," "documentary style."

-   **f/11 - f/22 (Narrow Aperture):**
    -   **Vibe:** Deep DoF, sharp from foreground to background, detailed, architectural, landscape.
    -   **Flux2 Interpretation:** Maximum depth of field, nearly everything in focus. Suitable for landscapes, architecture, or scenes where overall sharpness is critical. Potential for diffraction softening at extreme values.
    -   **Use Case:** "Sweeping landscape," "detailed architectural facade," "group photo with clear background."

## III. Sensor Type / Film Stock Characteristics

-   **Medium Format Digital (e.g., Hasselblad, Fuji GFX):**
    -   **Vibe:** High resolution, superb detail, rich tonality, often a "painterly" or "3D pop" look due to larger sensor and unique lens characteristics. Distinctive smooth roll-off from in-focus to out-of-focus areas.
    -   **Flux2 Interpretation:** Emphasize fine detail, subtle color gradations, expanded dynamic range, and a characteristic "depth" to the image. Simulate the slightly shallower DoF for a given focal length/aperture compared to full-frame.
    -   **Use Case:** "Editorial fashion," "luxury product," "fine art portrait."

-   **35mm Full Frame Digital (e.g., Canon 5D, Sony A7R):**
    -   **Vibe:** Versatile, excellent low-light performance, classic photographic look. Good balance of resolution and depth control.
    -   **Flux2 Interpretation:** Standard high-quality digital look, clean, balanced. Emphasize realistic light rendition and accurate color.
    -   **Use Case:** "General photography," "event coverage," "cinematic stills."

-   **Film Stock (e.g., Kodak Portra 400):**
    -   **Vibe:** Organic, warm skin tones, fine grain, subtle color shifts, forgiving highlights. Nostalgic.
    -   **Flux2 Interpretation:** Simulate film grain structure (subtle), characteristic color palette (e.g., Portra's warm greens, muted blues, excellent skin tones), and gentle highlight roll-off. Less digital "perfection."
    -   **Use Case:** "Nostalgic portrait," "organic fashion editorial," "warm and inviting scene."

-   **Film Stock (e.g., Fuji Velvia 50 - Slide Film):**
    -   **Vibe:** Hyper-real, vivid saturation, high contrast, fine grain, sharp. Punchy.
    -   **Flux2 Interpretation:** Simulate intense color saturation, strong contrast, sharp details. Can be used for vibrant landscapes or commercial product shots where "pop" is desired.
    -   **Use Case:** "Vibrant landscape," "punchy product photography."