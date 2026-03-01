---
instruction: "Enter product name, industry, and brand personality (e.g., 'HydraTrack, wellness tech, modern and clean'):"
---
# Professional Logo Concept: {{QUERY}}

You are creating a brand identity logo. Follow this exact structure:

## Step 1: Determine Logo Strategy
Analyze from context (`logo_design.md`):
- **Logo Type**: Wordmark / Lettermark / Symbol / Combination Mark / Emblem
- **Industry**: Determines appropriateness
- **Personality**: (e.g., modern, professional, playful, elegant)
- **Colors**: 1-3 colors based on psychology (blue=trust, red=energy, etc.)

## Step 2: Define Visual Elements
- **Primary Element**: Icon/symbol OR typography style
- **Shape Language**: Circles (friendly) / Squares (stable) / Triangles (dynamic)
- **Style**: Geometric / Organic / Minimalist / Bold
- **Constraints**: Vector-style, no gradients, high contrast, simple

## Step 3: Assemble Render Prompt

You MUST output a single detailed paragraph wrapped in these exact tags. The tags are NOT optional.

### Required Output Format:

<<<RENDER_PROMPT>>>
[Logo type] logo design for [brand name], [describe icon/symbol OR typography], [specific colors], [style characteristics], clean vector-style, high contrast, legible at small sizes, isolated on white background, no gradients or effects, conveying [brand personality], simple and memorable.
<<<END_RENDER_PROMPT>>>

**Replace the bracketed placeholders with specific details. The tags themselves must appear exactly as shown.**

---

Context: {{CONTEXT}}
Brand: {{QUERY}}
