# Multimodal Prompt Strategies: Optimizing Interpretation for Visual Reasoning Models

This document outlines best practices and specific prompt engineering techniques for effectively querying multimodal models (like Llava:34b) to extract accurate and structured interpretations from 2D floor plan images. The goal is to maximize the model's ability to identify, classify, and spatially reason about architectural elements.

## I. Strategies for Structured Extraction

-   **Explicit Task Definition:** Always begin the prompt by clearly stating the desired task (e.g., "Analyze this floor plan and identify...", "Extract the following information...").
    -   **Keywords:** `analyze`, `identify`, `extract`, `describe`, `locate`
-   **Step-by-Step Guidance:** For complex interpretations, break down the task into sequential steps. This can guide the model's reasoning process.
    -   **Example:** "First, identify all distinct rooms. Second, for each room, describe its type. Third, list all architectural elements within that room."
-   **Reference to Knowledge Bases:** Direct the model to consult specific knowledge bases for classification or definitions.
    -   **Example:** "Use the `architectural_symbol_atlas.md` to classify all identified symbols."
-   **Constraint-Based Prompting:** Specify what *not* to include or what assumptions to make.
    -   **Example:** "Assume standard residential furniture sizes unless otherwise specified."

## II. Optimizing for Floor Plan Interpretation

-   **Element-Specific Queries:**
    -   **Walls:** "Locate all walls and describe their layout, distinguishing between thick (structural) and thin (partition) lines."
    -   **Doors:** "Identify all doors, indicating their type (swing, pocket, bi-fold) and direction of swing where shown."
    -   **Windows:** "Find all windows and classify their types (e.g., fixed, casement, bay)."
    -   **Rooms:** "Identify and label each room (e.g., Living Room, Kitchen, Bedroom). For each room, provide its approximate dimensions and list adjacent rooms/areas."
-   **Dimension Extraction:**
    -   "Extract any explicit dimensions visible on the floor plan, specifying units if provided."
    -   "If no dimensions are explicitly given, estimate approximate room sizes based on standard residential layouts."
-   **Contextual Reasoning:**
    -   "Based on typical room functions, infer the most likely use of unlabeled spaces."

## III. Output Formatting

-   **Structured Text Output:**
    -   "Provide your analysis in a clear, bullet-point list, grouping information by room."
-   **JSON Output:**
    -   "Output the extracted data as a JSON object, with keys for 'rooms', 'walls', 'doors', 'windows', and their respective attributes (type, location, dimensions, orientation)."
    -   **Reference:** `output_formatting_rules.md` for specific JSON schema requirements.

## IV. Error Handling & Ambiguity

-   **Flag Ambiguity:** "If any architectural symbols or areas are ambiguous, describe the ambiguity and your best interpretation."
-   **Confidence Scoring:** "Indicate your confidence level for critical interpretations (e.g., room type, wall type)."

---

**General Principles for Multimodal Prompting:**

-   **Clarity & Conciseness:** Prompts should be clear, concise, and unambiguous.
-   **Iterative Refinement:** Be prepared to refine prompts based on initial interpretation results.
-   **Leverage External Knowledge:** Direct the model to utilize specific knowledge bases to enhance its interpretation capabilities.