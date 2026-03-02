---
instruction: "Describe your brand briefly (e.g., 'Vortex, urban backpacks, bold and modern'):"
---
# Logo Concept: {{QUERY}}

Your task: Transform the user's brief into a professional logo concept by applying design principles from the context.

## Phase 1: Interpret the Brand

From the user's input, determine:
- **Industry context** → What logo type is most appropriate?
- **Brand personality** → What colors convey this feeling?
- **Visual essence** → What symbol or typography style fits?

Reference `logo_design.md` to inform every decision:
- Logo types (wordmark, symbol, combination, emblem)
- Color psychology (blue=trust, orange=energy, black=sophistication)
- Typography guidelines (serif=traditional, sans-serif=modern)
- Shape language (circles=friendly, angles=dynamic)

## Phase 2: Design the Logo

Synthesize your analysis into a clear visual concept:
- Choose 1-2 colors based on psychology and industry
- Define the primary visual element (icon OR distinctive typography)
- Apply simplicity, memorability, and versatility principles
- Consider how it works at small sizes and in black & white

## Phase 3: Assemble the Final Render Prompt

Create a single cohesive description that integrates all elements: logo type, brand name, detailed visual element description (icon/symbol OR typography), specific colors with their meaning, style characteristics, and brand personality conveyed.

The prompt must be wrapped within the tags shown below:

<<<RENDER_PROMPT>>>
[{Logo type} logo design for {brand name}, {detailed description of icon/symbol OR typography style}, {specific colors}, {style: geometric/organic/minimalist/bold}, clean vector illustration, high contrast, simple and memorable, professional brand identity, isolated on white background, conveying {brand personality}]
<<<END_RENDER_PROMPT>>>

---

Context: {{CONTEXT}}
User Input: {{QUERY}}
