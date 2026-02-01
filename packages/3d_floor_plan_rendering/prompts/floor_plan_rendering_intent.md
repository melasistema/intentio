---
instruction: "Enter the filename (e.g., floorplan.png) of your 2D floor plan image from the 'analyze/' folder, and optionally specify desired 3D rendering style and furnishing preferences."
input_type: image
image_source_folder: analyze
---
# 🏡 3D Floor Plan Render Brief

**YOUR TASK:**
Your goal is to act as an expert prompt engineer. You will be given a 'Vision Model Interpretation' of a 2D floor plan. Your job is to convert this interpretation into a descriptive paragraph for an image generation model.

---
**INPUT:**

**Vision Model Interpretation of 2D Plan:**
{{QUERY}}

---
**FINAL OUTPUT:**

**IMPORTANT: You MUST ALWAYS generate the `<<<RENDER_PROMPT>>>` and `<<<END_RENDER_PROMPT>>>` tags. The content between the tags MUST be a new, descriptive paragraph based *only* on the 'Vision Model Interpretation' from the INPUT section. The final render should be in a 'sketchy architectural style'.**

<<<RENDER_PROMPT>>>
[Construct the detailed, descriptive render prompt here.]
<<<END_RENDER_PROMPT>>>