---
instruction: "Enter your product name, what it does for its user, and how it looks (e.g., 'Lumo, a reading lamp that clips to a book so you can read in bed without waking anyone. A slim white aluminium arm with a warm light.'):"
render: true
uses: product, logo
---
# Landing Page Design: {{QUERY}}

Your task: design the hero section of a landing page for this product, then describe it for an image model. Apply the principles of `landing_page.md` from the context.

Use only what the user states about the product; invent no features.

## Phase 1: Understand the Product
- **Name** → What is the product called?
- **Outcome** → What is the one result its user gets?
- **Look** → What is its shape, and what is its main color?

## Phase 2: Write the Words
Follow "The headline" and "The call to action":
- **Headline** → The outcome in at most five plain words, without the product name
- **Button** → Two words, verb first

The page shows these two texts and the logo. It shows no other text.

## Phase 3: Design the Page
Follow "Layout of a hero section" and "Visual style of the page". Choose one of each:
- **Photograph**: on the right, for an upright or compact product; across the lower half, for a low, wide product
- **Background**: warm off-white for a dark or colored product; soft grey for a white one
- **Button color**: the main color of the product

## Phase 4: Assemble the Final Render Prompt
Fill the template below with your decisions from Phases 1 to 3, one short phrase for each part in braces. Every part must appear, in this order. The result is one sentence without braces.

Each of the two texts stays inside its double quotes, so the image model renders it.

The image model is shown two images: the first is the product, the second is its logo. The template points to them, so neither is described again.

## Answer Structure
Answer in exactly this form: five short lines, then the render prompt between its two tags. The tags are required: without them no image can be rendered. Write nothing after the closing tag.

**Name**: {the product name}
**Headline**: {at most five words}
**Button**: {two words}
**Product**: {its main color and what it is, in two or three words}
**Page**: {where the photograph sits, background color, button color}

<<<RENDER_PROMPT>>>
Landing page design for a product website, flat front view of the whole page, {background color} background, the logo from the second image at the top left, large headline reading "{headline}", below it one {button color} rounded button reading "{button}", {where the photograph sits} a product photograph of the {main color and what the product is, in two or three words} from the first image, soft studio lighting, generous empty space, minimal modern web design, clean sans-serif typography
<<<END_RENDER_PROMPT>>>

---

User Input: {{QUERY}}

Answer with the five lines, then the render prompt between <<<RENDER_PROMPT>>> and <<<END_RENDER_PROMPT>>>.
