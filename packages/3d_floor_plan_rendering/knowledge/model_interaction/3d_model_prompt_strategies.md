# 3D Model Prompt Strategies: Guiding Image Generation Models for Architectural Renders

This document outlines effective prompt engineering techniques for guiding image generation models to produce high-quality, stylized 3D furnished sketches or views based on interpreted floor plan data and user preferences. The goal is to translate structured architectural information into compelling visual renders.

## I. Structuring the 3D Render Prompt

-   **Start with Core Style:** Begin the prompt with the overarching rendering style and camera angle.
    -   **Example:** `3/4 isometric view, sketchy architectural drawing style`
-   **Architectural Elements:** Detail the walls, flooring, ceiling, doors, and windows based on the interpreted floor plan.
    -   **Example:** `interior of a [Room Type] with [Wall Material] walls, [Floor Material] flooring, [Ceiling Style] ceiling, [Door Type] door, [Window Type] window.`
-   **Furnishing Details:** Incorporate furniture items, their styles, materials, and placement.
    -   **Example:** `furnished with a [Furniture Style] sofa, a [Furniture Style] coffee table, and [Furniture Style] rug, arranged functionally.`
-   **Lighting & Atmosphere:** Specify the desired lighting conditions and overall mood.
    -   **Example:** `soft natural daylight streaming from windows`, `ambient interior lighting`, `cozy atmosphere`
-   **Stylistic Modifiers:** Add keywords to reinforce the chosen render style.
    -   **Example:** `hand-drawn lines`, `watercolor effect`, `clean minimalist aesthetics`, `photorealistic detail`
-   **Negative Conditioning:** Crucially, specify what *not* to render to maintain style and avoid unwanted elements (even if the model understands a separate negative prompt, reinforcing in the positive can be beneficial).
    -   **Example:** `avoiding harsh shadows`, `no unrealistic textures`, `no floating furniture`, `not photorealistic` (if sketchy style)

## II. Prompting for Specific Details

-   **Room-Specific Furnishing:** Tailor furniture descriptions to identified room types.
    -   **Reference:** `room_type_library.md`
-   **Furniture Style Consistency:** Ensure all furniture tokens align with the chosen style.
    -   **Reference:** `furniture_style_catalog.md`
-   **Material Descriptions:** Use detailed material tokens for walls, floors, and furniture.
    -   **Example:** `polished concrete floor`, `light oak wood paneling`, `velvet upholstered sofa`
-   **Camera & Perspective:**
    -   **Reference:** `3d_rendering_techniques.md` for angle and projection types.

## III. Managing Complexity

-   **Layered Prompting (Conceptual):** For very complex plans, consider generating a prompt that builds up elements layer by layer (e.g., first walls, then doors/windows, then furniture) or room by room, then combining them.
-   **Conciseness:** While detailed, prompts should remain as concise as possible to avoid token limits or diluting the message.

## IV. Output Format & Quality

-   **Image Generation Model Compatibility:** Ensure the final prompt structure and keywords are compatible with the target image generation model's capabilities.
-   **Resolution & Aspect Ratio:** Embed desired output resolution and aspect ratio in the prompt if the model supports it or in the generation request parameters.

---

**General Principles for 3D Model Prompting:**

-   **Clarity from Interpretation:** The prompt's strength comes from the precise interpretation provided by the multimodal model.
-   **Reinforce Style:** Use consistent keywords and negative conditioning to ensure the desired rendering style.
-   **Functional & Aesthetic Balance:** Balance functional requirements (furniture placement) with aesthetic goals (style, lighting).