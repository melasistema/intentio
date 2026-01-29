# Lighting Library: Crafting Mood with Illumination for Flux2

This library defines key lighting setups, their technical characteristics, and the emotional impact they create. Flux2 should interpret these definitions to render realistic and mood-appropriate illumination.

## I. Light Quality (Hard vs. Soft)

-   **Hard Light:**
    -   **Technical:** Small, direct light source relative to the subject (e.g., bare bulb, direct sun at noon, focused spotlight). Creates well-defined, sharp-edged shadows.
    -   **Mood:** Dramatic, intense, stark, high contrast, graphic. Can evoke tension, mystery, or realism.
    -   **Flux2 Interpretation:** Render sharp shadow lines, high contrast between illuminated and shadowed areas. Specular highlights will be small and intense.

-   **Soft Light:**
    -   **Technical:** Large, diffused light source relative to the subject (e.g., overcast sky, large softbox, light bounced off a large white surface). Creates gradual transitions from light to shadow, with soft-edged or imperceptible shadows.
    -   **Mood:** Gentle, ethereal, calming, flattering, low contrast. Can evoke serenity, beauty, or intimacy.
    -   **Flux2 Interpretation:** Render diffused shadow edges, smooth tonal gradations. Specular highlights will be large and soft.

## II. Lighting Patterns & Setups

-   **Flat Lighting:**
    -   **Technical:** Light source positioned directly in front of the subject, illuminating it evenly. Minimal shadows.
    -   **Mood:** Direct, open, honest, sometimes clinical or bland. Reduces texture and dimension.
    -   **Flux2 Interpretation:** Even illumination across the subject's front plane, minimal discernible shadows. Reduces appearance of wrinkles or blemishes.

-   **Butterfly Lighting (Paramount Lighting):**
    -   **Technical:** Single main light placed directly in front of and above the subject, casting a small, butterfly-shaped shadow under the nose.
    -   **Mood:** Glamorous, elegant, flattering (especially for high cheekbones), classic beauty.
    -   **Flux2 Interpretation:** Distinctive "butterfly" shadow under the nose, well-defined cheekbones, catchlights high in the eyes.

-   **Loop Lighting:**
    -   **Technical:** Main light positioned slightly to the side and above the subject, creating a small shadow loop extending from the side of the nose.
    -   **Mood:** Natural, slightly more dimension than butterfly, widely flattering.
    -   **Flux2 Interpretation:** A clear, downward-pointing shadow loop adjacent to the nose, defining facial contours subtly.

-   **Rembrandt Lighting:**
    -   **Technical:** Main light placed at a 45-degree angle to the subject and slightly elevated, creating a distinct triangle of light on the cheek opposite the light source (the "Rembrandt patch"). Requires precise light placement and sometimes a fill light.
    -   **Mood:** Dramatic, moody, classic, artistic, intellectual, mysterious. Often used for male portraits.
    -   **Flux2 Interpretation:** A clear, inverse triangle of light on the shadowed cheek, strong definition of facial planes, distinct transition from light to shadow.

-   **Split Lighting:**
    -   **Technical:** Main light positioned directly to one side of the subject (90 degrees), illuminating exactly half the face while the other half remains in shadow.
    -   **Mood:** Intense, dramatic, mysterious, edgy, high contrast. Can signify duality or strong personality.
    -   **Flux2 Interpretation:** A sharp line dividing the face into brightly lit and deeply shadowed halves.

-   **Rim Lighting (Hair Light/Kicker):**
    -   **Technical:** Light placed behind and to the side of the subject, aimed at the back of the head/shoulders. Used to separate the subject from the background.
    -   **Mood:** Adds depth, dimension, ethereal glow, angelic, separates subject.
    -   **Flux2 Interpretation:** A bright outline of light along the contours of the subject's hair, shoulders, or edges, creating separation and a luminous effect.

-   **Backlighting:**
    -   **Technical:** Main light source directly behind the subject, facing the camera. Often creates a silhouette or a strong rim light effect.
    -   **Mood:** Dramatic, mysterious, creates silhouettes, ethereal, sense of isolation or hope.
    -   **Flux2 Interpretation:** Subject appears dark or as a silhouette against a bright background. Strong lens flares can occur if the light source is in frame.

## III. Lighting Ratios (Key-to-Fill)

-   **1:1 Ratio (No Fill):**
    -   **Technical:** Key light only. Shadows are black, no detail.
    -   **Mood:** Stark, high drama, conceptual.
    -   **Flux2 Interpretation:** Pure black shadows with no visible detail.

-   **2:1 Ratio (Subtle Fill):**
    -   **Technical:** Key light is twice as bright as the fill light. Shadows are present but contain some detail.
    -   **Mood:** Natural, soft contrast, subtle dimension. Often used for fashion and beauty.
    -   **Flux2 Interpretation:** Shadows are gently lifted, allowing for some texture and detail to be visible, but still clearly distinct from highlights.

-   **4:1 Ratio (Moderate Contrast):**
    -   **Technical:** Key light is four times brighter than the fill light. Shadows are noticeable and contribute to mood.
    -   **Mood:** Classic portraiture, cinematic, slightly dramatic.
    -   **Flux2 Interpretation:** Shadows are deeper but still reveal information, creating a more pronounced sense of form and depth.

-   **8:1 Ratio (High Contrast):**
    -   **Technical:** Key light is eight times brighter than the fill light. Shadows are dark with minimal detail.
    -   **Mood:** Highly dramatic, moody, chiaroscuro.
    -   **Flux2 Interpretation:** Shadows are very dark, with limited detail, emphasizing the starkness between light and dark.

## IV. Color Temperature

-   **Warm (2700K - 3200K):**
    -   **Technical:** Tungsten, candle light, golden hour sun.
    -   **Mood:** Cozy, intimate, nostalgic, romantic.
    -   **Flux2 Interpretation:** Render a warm, orange/yellow color cast.

-   **Neutral (5500K - 5600K):**
    -   **Technical:** Daylight, electronic flash.
    -   **Mood:** Accurate, clean, clinical, natural.
    -   **Flux2 Interpretation:** Render colors as perceived in neutral daylight, without dominant color casts.

-   **Cool (6500K - 8000K):**
    -   **Technical:** Overcast sky, open shade, fluorescent lights.
    -   **Mood:** Cool, serene, modern, sometimes stark or eerie.
    -   **Flux2 Interpretation:** Render a cool, blue color cast.