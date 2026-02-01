# Architectural Symbol Atlas: Interpretation Guide for Multimodal Models

This atlas provides a comprehensive guide to interpreting common architectural symbols found in 2D floor plans. It is designed to train multimodal models on recognizing, classifying, and extracting spatial data from these visual cues, enabling accurate reconstruction of interior layouts.

## I. Wall Representations

-   **Solid Lines (Thick):**
    -   **Interpretation:** Represents structural load-bearing walls.
    -   **Keywords for Model Prompt:** `thick structural wall`, `load-bearing partition`
-   **Solid Lines (Thin):**
    -   **Interpretation:** Represents non-load-bearing partitions or interior walls.
    -   **Keywords for Model Prompt:** `thin interior wall`, `non-load-bearing partition`
-   **Dashed Lines:**
    -   **Interpretation:** Represents overhead elements like beams, archways, or future construction.
    -   **Keywords for Model Prompt:** `overhead beam`, `archway overhead`, `future construction indication`

## II. Door Symbols

-   **Swing Door (Arc indicating swing):**
    -   **Interpretation:** Indicates a standard hinged door. Arc shows direction of swing.
    -   **Keywords for Model Prompt:** `standard swing door`, `hinged door opens [inward/outward]`
-   **Pocket Door (Line into wall cavity):**
    -   **Interpretation:** Door slides into a wall cavity.
    -   **Keywords for Model Prompt:** `pocket sliding door`, `concealed door`
-   **Bi-fold Door (Zig-zag lines):**
    -   **Interpretation:** Door folds in sections.
    -   **Keywords for Model Prompt:** `bi-fold door`, `folding partition`

## III. Window Symbols

-   **Single Line (between two walls):**
    -   **Interpretation:** Simple window, often a fixed pane.
    -   **Keywords for Model Prompt:** `fixed window pane`, `simple window`
-   **Double/Triple Lines (between two walls):**
    -   **Interpretation:** More detailed window, often indicating double-hung, casement, or sliding.
    -   **Keywords for Model Prompt:** `double-pane window`, `casement window`, `sliding window`
-   **Bay Window (Protrusion from wall):**
    -   **Interpretation:** Window structure extending outwards from the main wall.
    -   **Keywords for Model Prompt:** `bay window`, `projecting window alcove`

## IV. Room & Area Labels

-   **Text Labels (e.g., "Living Room," "Kitchen"):**
    -   **Interpretation:** Defines the function and boundaries of a specific space.
    -   **Keywords for Model Prompt:** `[Room Type] area`, `designated [Room Type]`
-   **Dimension Lines:**
    -   **Interpretation:** Indicates measurements of walls, rooms, or features.
    -   **Keywords for Model Prompt:** `dimensions [Length] by [Width]`, `measured length`

## V. Plumbing Fixtures

-   **Sink (Rectangle with circles):**
    -   **Interpretation:** Identifies a sink unit.
    -   **Keywords for Model Prompt:** `kitchen sink`, `bathroom vanity with sink`
-   **Toilet (Oval with rectangle):**
    -   **Interpretation:** Identifies a toilet.
    -   **Keywords for Model Prompt:** `standard toilet fixture`
-   **Shower (Square/rectangle with 'X'):**
    -   **Interpretation:** Identifies a shower stall.
    -   **Keywords for Model Prompt:** `enclosed shower stall`, `walk-in shower`

## VI. Stairs

-   **Parallel Lines with Arrow:**
    -   **Interpretation:** Indicates a staircase, arrow shows direction of ascent.
    -   **Keywords for Model Prompt:** `staircase leading upwards`, `wooden stairs`, `modern staircase`

---

**General Interpretation Principles for Multimodal Models:**

-   **Contextual Understanding:** Symbols should be interpreted within the context of the overall floor plan (e.g., a small rectangle with an 'X' in a bathroom is a shower, not a window).
-   **Hierarchical Parsing:** Prioritize identifying major elements (walls, rooms) before detailing smaller features.
-   **Dimension Extraction:** If present, extract and use explicit dimensions to inform the 3D model's scale.