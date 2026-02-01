# Output Formatting Rules: Standardizing Data Exchange for Multimodal Models

This document defines the standard formats for data output from multimodal models and for inputs to image generation models within the 3D Floor Plan Rendering environment. Consistent formatting is crucial for seamless data exchange and automation of the interpretation and rendering pipeline.

## I. Structured Output from Multimodal Model (e.g., `interpret_floor_plan` action)

The multimodal model's interpretation of a 2D floor plan should ideally be presented as a JSON object, ensuring easy parsing and manipulation by subsequent INTENTIO modules.

-   **Root Object:** Represents the entire floor plan.
-   **Keys:**
    -   `"global_dimensions"`: (Optional) Overall width and height of the plan if discernible.
    -   `"rooms"`: An array of room objects.
    -   `"walls"`: An array of wall objects (for overall structure).
    -   `"doors"`: An array of door objects.
    -   `"windows"`: An array of window objects.
    -   `"other_elements"`: (Optional) Any other identified elements (e.g., stairs, built-ins).

### Example JSON Schema (simplified):

```json
{
  "global_dimensions": { "width": "10m", "height": "8m", "units": "meters" },
  "rooms": [
    {
      "id": "room_01",
      "type": "Living Room",
      "labels_detected": ["Living Room"],
      "approx_dimensions": { "length": "5m", "width": "4m" },
      "elements": ["sofa", "coffee table"],
      "connections": ["Kitchen", "Hallway"],
      "wall_segments": [ /* array of wall segment IDs */ ]
    },
    {
      "id": "room_02",
      "type": "Kitchen",
      "labels_detected": ["Kitchen"],
      "approx_dimensions": { "length": "3m", "width": "3m" },
      "elements": ["sink", "stove", "refrigerator"],
      "connections": ["Living Room"],
      "wall_segments": [ /* array of wall segment IDs */ ]
    }
  ],
  "walls": [
    { "id": "wall_001", "type": "thick_structural", "length": "5m", "orientation": "south" }
  ],
  "doors": [
    { "id": "door_001", "type": "swing_door", "location": "Living Room - Hallway", "orientation": "inward" }
  ],
  "windows": [
    { "id": "window_001", "type": "double_pane", "location": "Living Room", "width": "2m" }
  ],
  "interpretation_notes": "Scale estimated from standard room sizes."
}
```

## II. Prompt String for Image Generation Model (e.g., `furnish_and_stylize_plan` action)

The final prompt string passed to the image generation model should be a single, cohesive text string.

-   **Structure:** Should generally follow the guidelines in `3d_model_prompt_strategies.md`, beginning with rendering style and camera, detailing architectural elements and furnishings, and incorporating negative conditioning phrases directly within the positive prompt if `--neg` is not supported.
-   **Keywords:** Utilize specific keywords from various knowledge bases (e.g., `furniture_style_catalog.md`, `3d_rendering_techniques.md`) to guide the model.
-   **Clarity & Detail:** Be as descriptive as possible while remaining concise.

## III. Image Output from Image Generation Model (`render_3d_sketch` action)

-   **Format:** Standard image formats (PNG, JPG)
-   **Resolution:** User-defined or default (e.g., 1024x768, 1920x1080).
-   **Metadata:** (Optional) Include embedded metadata about the prompt used, style, and interpretation notes.

---

**General Formatting Principles:**

-   **Parsable:** All structured outputs must be easily parsable by automated scripts.
-   **Human-Readable:** Text outputs should also be clear and understandable to human users.
-   **Consistency:** Adhere strictly to defined schemas and conventions for all data exchanges.
