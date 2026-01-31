# Color Palette Atlas: Model Interpretation for Cartoon Universe

This atlas defines the permissible color ranges and application rules for the Cartoon Universe, ensuring a consistent and vibrant visual identity.

## I. Core Color Philosophy

-   **Limited & Distinct:** The universe operates on a curated set of colors, avoiding subtle shifts or broad gradients within a single object.
-   **Vibrant & Saturated:** Colors should be clear, bright, and slightly exaggerated, contributing to an energetic and cheerful mood.
-   **Flat Application:** Colors are applied in flat, unbroken fields, without texture or realistic light interaction.

## II. Primary Palette (Examples)

-   **Reds:**
    -   `Vibrant cherry red`, `bold crimson`, `playful ruby`
    -   HEX: #E50000, #CC0000
-   **Blues:**
    -   `Sky blue`, `ocean blue`, `royal blue`
    -   HEX: #00BFFF, #007FFF
-   **Greens:**
    -   `Lime green`, `forest green`, `grass green`
    -   HEX: #32CD32, #228B22
-   **Yellows:**
    -   `Sunny yellow`, `bright lemon yellow`, `goldenrod`
    -   HEX: #FFD700, #FFEA00
-   **Pinks:**
    -   `Bubblegum pink`, `hot pink`
    -   HEX: #FF69B4, #FF1493
-   **Neutrals:**
    -   `Pure white`, `light grey`, `charcoal grey`, `jet black`
    -   HEX: #FFFFFF, #CCCCCC, #333333, #000000

## III. Color Application Rules

-   **Flat Color Fills:** Each distinct area of a character, object, or background element should be filled with a single, uniform color.
    -   **Keywords:** `flat color fills`, `solid color blocks`, `no gradients`, `no textures`
-   **Bound by Line Art:** Colors must always be contained precisely within the boundaries of the `line_art_rules.md`. No color bleed.
-   **No Photo-Realism:** Avoid any color treatments that suggest photographic accuracy, natural skin tones, or complex environmental lighting.
    -   **Negative Keywords:** `no natural skin tones`, `no photographic colors`, `no subtle color variations`

## IV. Contrast & Legibility

-   **Strong Contrast:** Colors should have sufficient contrast to ensure clear separation of elements and enhance readability.
-   **Readability:** Text (if any) and important details should be highly legible against their background color.

---

**General Color Principles for the Image Generation Model (Cartoon Universe):**

-   **Stylized, Not Realistic:** The goal is a stylized, graphic color application, not an imitation of reality.
-   **Strict Adherence:** Any deviation towards natural or photographic coloring must be strongly negated.
-   **Enforce with Style Lock:** `enforce_color_palette` in `manifest.md` ensures these rules are followed.