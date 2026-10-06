---
instruction: "Describe your product and what it does (e.g., 'a lunch box that heats a meal' or 'a pen for architects with a scale ruler in the barrel'):"
render: true
keep_as: product
---
# Product Visualization: {{QUERY}}

Your task: design how this product looks, then describe it as a mockup for an image model. Apply the principles of `product_mockup.md` from the context.

Keep every material, color and feature the user names. Where the description is silent, decide as a product designer would.

## Phase 1: Understand the Product

- **Function** → What does it do, and what does it hold or who holds it?
- **Size** → What footprint and what thickness follow from that? Compare each with a familiar object.
- **Key feature** → Which single feature makes it different?

## Phase 2: Design the Object

Follow "Proportion and scale" and "Design language of a contemporary product":
- **Shape** → What the object is, and its silhouette and proportions in plain geometric words
- **Materials** → One main material with its finish, and at most one accent
- **Key feature** → Its simplest form: where it sits and what it looks like
- **Function cue** → The one detail that shows what the product does. A product that holds something is shown open, with its content inside.

Check each word against "Words that mislead an image model", and replace the ones that would draw the wrong picture.

## Phase 3: Define the Presentation

Choose one of each:
- **Angle**: three-quarter view from above for most products and for every low, flat one; front view for a tall product; close-up for a detail
- **Lighting**: soft studio for clean and professional, side light for texture, natural for lifestyle
- **Background**: isolated on a neutral background, or one simple context

## Phase 4: Assemble the Final Render Prompt

Fill the template below with your decisions from Phases 1 to 3, one short phrase for each part in braces. Every part must appear, in this order. The result is one sentence without braces.

It describes only what is visible in the image. Reasons belong in the phases above.

If the product shows text, enclose it in double quotes (e.g., display reading "24°C") so the image model renders it. Otherwise name no text, numbers or brand at all.

## Answer Structure

Answer in exactly this form: four short lines, then the render prompt between its two tags. The tags are required: without them no image can be rendered. Write nothing after the closing tag.

**Product**: {what it does and what it holds, in one sentence}
**Size**: {footprint and thickness, each compared with a familiar object}
**Design**: {shape, materials, key feature, function cue}
**Presentation**: {angle, lighting, background}

<<<RENDER_PROMPT>>>
Professional product photography of {the product by its common name, or by its shape if the name is only "box", "case" or "device"; with its proportions}, {footprint and thickness, each compared with a familiar object}, {main material and finish}, {accent}, {key feature and where it sits}, {function cue}, {angle}, {lighting}, {background}, clean composition, {overall style}
<<<END_RENDER_PROMPT>>>

---

User Input: {{QUERY}}

End your answer with the render prompt between <<<RENDER_PROMPT>>> and <<<END_RENDER_PROMPT>>>.
