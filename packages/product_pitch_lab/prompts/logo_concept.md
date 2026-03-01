---
instruction: "Enter product name, industry, and brand personality (e.g., 'HydraTrack, wellness tech, modern and clean'):"
---
# Logo Concept: {{QUERY}}

Your ONLY task: Generate a logo render prompt. No analysis. No explanation. Just the prompt.

## Step 1: Extract Brand Essence
- Logo type (wordmark/symbol/combination)
- 1-2 key colors
- Visual element (icon or typography style)

## Step 2: Apply Logo Principles
Reference `logo_design.md` for:
- Shape psychology
- Color meaning
- Style appropriateness

## Step 3: OUTPUT THE RENDER PROMPT

This is the ONLY output required. Do not write anything before or after these tags:

<<<RENDER_PROMPT>>>
[Logo type] logo design for [brand name], [icon/symbol description OR typography description], [specific hex colors], [shape language: geometric/organic/minimalist], clean vector illustration style, high contrast, simple and memorable, isolated on white background, professional brand identity aesthetic, conveying [brand personality in 2-3 words]
<<<END_RENDER_PROMPT>>>

THE TAGS ARE MANDATORY. Output nothing else except what's shown above.

Context: {{CONTEXT}}
Brand: {{QUERY}}
