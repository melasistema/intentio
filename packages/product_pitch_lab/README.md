# Product Pitch Lab Package

A cognitive instrument for validating, refining, and visualizing product ideas using established business frameworks and visual mockup generation.

This package operates as a suite of analytical and creative "commands" designed for entrepreneurs, product managers, and innovators who want to stress-test their ideas before investing significant resources.

## How It Works

When you select a prompt template (a "command") in interactive mode, you'll receive specific guidance on what information to provide. The system analyzes your input using curated business knowledge—lean startup principles, market analysis frameworks, positioning strategies, and visual design theory.

## Available Commands (Prompts)

### Analysis Commands (Text-Based)

*   **`validate_idea`** (Default)
    *   Critically evaluates your product concept for problem-solution fit, target audience clarity, and viability.
    *   *Instruction: Describe your product idea in 2-3 sentences:*

*   **`craft_pitch`**
    *   Generates a structured 30-second elevator pitch with hook, problem, solution, and value proposition.
    *   *Instruction: Enter your product name and what it does:*

*   **`competitor_analysis`**
    *   Performs SWOT-based competitive analysis and identifies differentiation opportunities.
    *   *Instruction: Describe your product and 2-3 competitors:*

*   **`business_model`**
    *   Creates a Lean Canvas breakdown covering problem, solution, customers, channels, revenue, and costs.
    *   *Instruction: Provide product concept and target market:*

*   **`default`**
    *   Allows open-ended questions about business frameworks and concepts in the knowledge base.
    *   *Instruction: Ask a question about product strategy or business frameworks:*

### Visualization Commands (Image Generation)

*   **`visualize_product`**
    *   Generates a professional product mockup concept based on your description.
    *   *Instruction: Describe the product you want to visualize:*

*   **`logo_concept`**
    *   Creates a brand identity visual concept aligned with your product's personality.
    *   *Instruction: Product name, industry, and brand personality (modern/classic/playful):*

## Example Usage

### Phase 1: Validation
1.  Start interactive mode: `./intentio interact`
2.  Select the `product_pitch_lab` space
3.  Select the `validate_idea` template (or it will be selected by default)
4.  Describe your product idea

```
INTENTIO > A smart water bottle that tracks hydration and reminds busy professionals to drink water throughout the day.
```

The system will provide critical analysis on problem clarity, target audience, competitive landscape, and potential concerns.

### Phase 2: Refinement
Based on validation feedback, refine your concept and craft your pitch:

```
# Switch to craft_pitch
INTENTIO > switch_prompt

# Select craft_pitch, then:
INTENTIO > HydraTrack - A smart water bottle for busy professionals that tracks hydration levels and sends gentle reminders via a mobile app.
```

### Phase 3: Visualization
Once your concept is refined, generate visual assets:

```
# Switch to visualize_product
INTENTIO > switch_prompt

# Select visualize_product, then:
INTENTIO > A sleek, modern water bottle with an LED display ring at the base showing hydration progress in blue. Minimalist design, brushed steel finish, touchscreen interface.
```

The system will:
1. Analyze your description using design principles
2. Generate an optimized image prompt
3. Display the manifest with `<<<RENDER_PROMPT>>>` tags
4. Ask: "Render this manifest? (yes/no/refine):"
5. If `yes` → Generate image via Ollama's diffusion model
6. Save to `renderer_images/` directory in the space

**Note**: Images are saved to `spaces/product_pitch_lab/renderer_images/` (not the package directory). The space must have this directory for rendering to work.

You can type `refine` to iterate on the prompt before rendering.

## What This Package Is

✅ A validation tool for stress-testing business ideas  
✅ A structured framework for building pitches  
✅ A concept visualization generator for early-stage mockups  
✅ An educational resource on business frameworks  

## What This Package Is Not

❌ A substitute for market research or customer interviews  
❌ A production-ready design tool  
❌ A comprehensive business consultant  
❌ A guarantee of product success  

The mockups are concept sketches, not final designs. The analysis is grounded in established frameworks but not a replacement for domain expertise or real-world validation.

## Tutorial

For a complete guide on how this package was designed and how to create your own cognitive spaces, see the tutorial: [`references/products-pitch-lab.md`](../../references/products-pitch-lab.md)
