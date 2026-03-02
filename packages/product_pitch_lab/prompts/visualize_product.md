---
instruction: "Describe your product simply (e.g., 'a smart water bottle' or 'urban backpack with tech features'):"
---
# Product Visualization: {{QUERY}}

Your task: Transform the user's description into a professional product mockup by applying visualization principles from the context.

## Phase 1: Interpret the Product

From the user's input, determine:
- **Product category** → What's the use context and target user?
- **Key features** → What should be visually emphasized?
- **Aesthetic direction** → What style matches the product positioning?

Reference `product_mockup.md` to inform decisions:
- Material communication (metal=premium, plastic=accessible, glass=refined)
- Form and silhouette appropriate to function
- Color palette that supports brand personality
- Features that differentiate from competitors

## Phase 2: Define Presentation

Apply professional photography principles:
- **Angle**: Front view for clarity, three-quarter for depth, close-up for detail
- **Lighting**: Studio for clean professional, dramatic for premium, natural for lifestyle
- **Environment**: Isolated (focus on product) or contextual (show use case)
- **Details**: Emphasize materials, textures, and differentiating features

## Phase 3: Assemble the Final Render Prompt

Synthesize your analysis into a single cohesive prompt that integrates all elements: product description with specific materials, key features emphasized, viewing angle, lighting approach, background, material qualities, and aesthetic direction.

The prompt must be wrapped within the tags shown below:

<<<RENDER_PROMPT>>>
[Professional product photography of {product with materials and finishes}, {key feature emphasized}, {viewing angle}, {lighting}, {background/environment}, clean composition, {material qualities}, emphasizing {primary value}, {overall aesthetic}]
<<<END_RENDER_PROMPT>>>

---

Context: {{CONTEXT}}
User Input: {{QUERY}}
