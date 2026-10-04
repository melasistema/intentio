# Product Pitch Lab Package

A cognitive instrument for validating, refining, and visualizing product ideas, grounded in a small knowledge base of business frameworks and visual design principles.

This package operates as a suite of "commands" that you run from the INTENTIO interactive mode. Five of them reason in text; two of them write a prompt for the local image model and render it. It is designed for founders, product managers, and students who want to stress-test an idea before investing in it.

## Quick Start

```bash
./intentio init product_pitch_lab --space=product_pitch_lab
./intentio interact
```

Select the `product_pitch_lab` space. The first time you enter it, INTENTIO ingests the knowledge base, then starts you on `validate_idea`.

## How It Works

Every command is a prompt template. When you select one, you are given a specific instruction for what to enter. The system then builds its answer from two sources of context:

*   **Pinned knowledge**: each analysis command names the knowledge files it depends on, and INTENTIO loads those files in full.
*   **Retrieved knowledge**: the passages of the knowledge base most similar to your input.

After each answer you choose the command for your next step, so a session naturally moves from validation to refinement to visualization.

## Available Commands (Prompts)

### Analysis Commands (Text)

*   **`validate_idea`** (Default)
    *   Critically evaluates a product idea: problem clarity, target customer, solution viability, the red flags that apply, the riskiest assumptions, and a verdict.
    *   Grounded in `problem_solution_fit.md` and `red_flags.md`.
    *   *Instruction: Describe your product idea in 2-3 sentences (what it is, who it is for, what problem it solves):*

*   **`craft_pitch`**
    *   Writes a 30-45 second elevator pitch in five parts: hook, problem, solution, unique value, call to action.
    *   Grounded in `elevator_pitch.md`.
    *   *Instruction: Enter your product name, what it does, and who it is for:*

*   **`competitor_analysis`**
    *   Maps the alternatives you describe, compares them with SWOT and a Jobs-to-be-Done table, and recommends a differentiation strategy.
    *   Grounded in `competitive_analysis.md`.
    *   *Instruction: Describe your product and 2-3 competitors or alternatives, with what you know about each:*

*   **`business_model`**
    *   Drafts a nine-block Lean Canvas, marks every entry as stated or assumed, and names the riskiest assumptions to test.
    *   Grounded in `lean_canvas.md` and `value_proposition.md`.
    *   *Instruction: Describe your product concept and its target market:*

*   **`default`**
    *   Answers open questions about the frameworks in the knowledge base (e.g., "What is the difference between a vitamin and a painkiller?").
    *   *Instruction: Ask a question about product strategy or business frameworks:*

### Visualization Commands (Image Generation)

*   **`visualize_product`**
    *   Turns a plain description into a professional product photography prompt, then offers to render it.
    *   Grounded in `product_mockup.md`.
    *   *Instruction: Describe your product simply (e.g., 'a smart water bottle' or 'urban backpack with tech features'):*

*   **`logo_concept`**
    *   Turns a brand brief into a logo prompt (type, colors, shape, typography), then offers to render it.
    *   Grounded in `logo_design.md`.
    *   *Instruction: Describe your brand briefly (e.g., 'Vortex, urban backpacks, bold and modern'):*

## Example Usage

### Phase 1: Validation

```
(Active Prompt Template: validate_idea)
Instruction: "Describe your product idea in 2-3 sentences (what it is, who it is for, what problem it solves):"

[validate_idea] > A smart water bottle that tracks hydration and reminds busy professionals to drink water throughout the day.
```

The system answers with a structured critique. It names the red flags that apply and ends with a verdict: **Promising**, **Needs rework**, or **Fatal flaw**.

### Phase 2: Refinement

A command stays active until you change it. Type `switch_prompt` and choose `craft_pitch`, `competitor_analysis`, or `business_model`:

```
[validate_idea] > switch_prompt

Available Prompt Templates for 'product_pitch_lab':
  1. business_model
  2. competitor_analysis
  3. craft_pitch
  4. default
  5. logo_concept
  6. validate_idea
  7. visualize_product
Enter the number of the prompt template to use: 3

[craft_pitch] > HydraTrack - a smart water bottle for busy professionals that tracks how much they drink and sends gentle reminders through a mobile app.
```

### Phase 3: Visualization

Type `switch_prompt`, choose `visualize_product` or `logo_concept`, and describe what you want to see:

```
[visualize_product] > A sleek, modern water bottle with an LED ring at the base showing hydration progress in blue. Minimalist design, brushed steel finish.
```

The system will:
1. Interpret your description using the design principles in the knowledge base
2. Write an image prompt wrapped in `<<<RENDER_PROMPT>>>` tags
3. Ask: `Render this image? (yes/no):`
4. On `yes`, generate the image with the local image model
5. Save it to `spaces/<your_space>/renderer_images/`

Not happy with the result? Answer `no`, choose the same command again, and describe the product differently.

## Notes

*   **The session has no memory between commands.** Each command sees only what you type into it, so repeat the product description when you move from one phase to the next.
*   **Images are saved in the space, not in this package.** The `renderer_images/` folder is created on the first render.
*   **Changing the knowledge.** A space is a copy of this package. To change what the space knows, edit the files under `spaces/<your_space>/knowledge/`, then run `./intentio ingest --space=<your_space>`. Only the files you changed are re-indexed.
*   **Context window.** `validate_idea` and `business_model` each load two knowledge files in full. If answers seem to ignore the frameworks, your model is probably truncating its input: raise `num_ctx` under `llm.options` in `config/app.local.php` (8192 is enough).

## What This Package Is

✅ A validation tool for stress-testing product ideas  
✅ A structured way to draft a pitch, a competitive analysis, and a Lean Canvas  
✅ A concept visualizer for early mockups and logo directions  
✅ A compact reference on business frameworks  

## What This Package Is Not

❌ A substitute for market research or customer interviews  
❌ A source of market data (it has none, and is told not to invent any)  
❌ A production-ready design tool  
❌ A guarantee of product success  

The mockups are concept sketches, not final designs. The analysis applies established frameworks to what you tell it; it knows nothing about your market that you have not written down.
