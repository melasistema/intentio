---
instruction: "Describe the product you want to visualize (e.g., 'A sleek smart water bottle with LED display'):"
---
# Professional Product Mockup: {{QUERY}}

You are creating a professional product visualization. Follow this exact structure:

## Step 1: Identify Product Elements
Analyze from context (`product_mockup.md`):
- **Materials**: (e.g., brushed aluminum, matte plastic, glass)
- **Key Feature**: Main visual differentiator
- **Form**: Overall shape and silhouette
- **Colors**: Palette (2-3 colors max)

## Step 2: Define Composition
- **Viewing Angle**: Front view / Three-quarter view / Close-up
- **Lighting**: Soft studio / Dramatic side / Natural daylight
- **Background**: Clean white / Blurred context / Lifestyle setting
- **Depth**: Subtle shadows for dimension

## Step 3: Assemble Render Prompt

You MUST output a single detailed paragraph wrapped in these exact tags. The tags are NOT optional.

### Required Output Format:

<<<RENDER_PROMPT>>>
Professional product photography of [product with specific materials and colors], [key feature emphasized], [viewing angle], [lighting style], [background], clean composition, avoiding clutter, emphasizing functionality and professional finish.
<<<END_RENDER_PROMPT>>>

**Replace the bracketed placeholders with specific details. The tags themselves must appear exactly as shown.**

---

Context: {{CONTEXT}}
Product: {{QUERY}}
