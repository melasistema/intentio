# Visual Interpretation Guides: Best Practices for Multimodal Models

This document provides best practices and specific strategies for prompting multimodal models to accurately interpret and extract structured information from 2D floor plan images. The goal is to maximize the model's understanding of architectural layouts and elements.

## I. General Principles for Visual Querying

-   **Be Explicit:** Clearly state the task (e.g., "Identify all rooms," "Extract dimensions").
-   **Structured Questions:** Break down complex interpretation tasks into smaller, structured questions.
-   **Contextual Clues:** Provide context if available (e.g., "This is a residential floor plan").
-   **Iterative Refinement:** If initial interpretation is unclear, refine queries to focus on specific ambiguous areas.

## II. Strategies for Element Identification

-   **Walls & Boundaries:**
    -   **Prompt Template:** "Describe the layout of the walls, identifying each room. For each room, specify its approximate boundaries and connections to other rooms or the exterior."
    -   **Focus Keywords:** `wall layout`, `room boundaries`, `connected rooms`
    -   **Reference:** `architectural_symbol_atlas.md` for wall types.
-   **Doors & Windows:**
    -   **Prompt Template:** "Locate all doors and windows. For each, describe its type (e.g., swing door, casement window) and approximate dimensions (width)."
    -   **Focus Keywords:** `door location`, `window types`, `opening dimensions`
    -   **Reference:** `architectural_symbol_atlas.md` for door/window symbols.
-   **Room Types & Labels:**
    -   **Prompt Template:** "Identify and label each distinct room area (e.g., Living Room, Kitchen, Bedroom). If explicit labels are present, use them."
    -   **Focus Keywords:** `room identification`, `room labels`, `functional areas`
    -   **Reference:** `room_type_library.md` for typical room functions.
-   **Fixtures & Appliances:**
    -   **Prompt Template:** "Identify any fixed fixtures or major appliances (e.g., sink, toilet, stove, refrigerator) within the floor plan and their location."
    -   **Focus Keywords:** `fixed fixtures`, `appliance identification`, `utility points`
    -   **Reference:** `architectural_symbol_atlas.md` for common symbols.

## III. Dimension & Scale Extraction

-   **Explicit Dimensions:**
    -   **Prompt Template:** "If explicit dimensions are provided on the floor plan, extract them for overall layout and individual rooms/elements. Indicate units (e.g., meters, feet)."
    -   **Focus Keywords:** `extract dimensions`, `measurements`, `room size`
-   **Approximate Scale (if no explicit dimensions):**
    -   **Prompt Template:** "Estimate the approximate scale of the floor plan based on typical room sizes or known object dimensions."
    -   **Focus Keywords:** `approximate scale`, `relative size`

## IV. Output Format Preferences

-   **Structured Text:**
    -   **Prompt Template:** "Provide the interpretation in a structured text format, listing each room with its detected elements and connections."
-   **JSON Output:**
    -   **Prompt Template:** "Output the extracted information as a JSON object, with keys for 'rooms', 'walls', 'doors', 'windows', and their respective attributes."
    -   **Reference:** `output_formatting_rules.md` for desired JSON schema.

---

**General Interpretation Principles for Multimodal Models:**

-   **Prioritize Clarity:** The model's primary goal is to provide clear, actionable insights from the visual input.
-   **Iterative Questioning:** It's often effective to ask a series of specific questions rather than one broad request for complex interpretations.
-   **Feedback Loop:** Use the results of interpretation to refine subsequent queries or prompt construction.