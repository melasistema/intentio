---
instruction: "Say where the logo goes on the product, how large, and in which color (e.g., 'small, in white, on the front of the lid'):"
render: true
uses: product, logo
keep_as: product
---
# Logo on the Product: {{QUERY}}

Your task: describe, for an image model, where the logo is printed on the product.

The image model is shown two images: the first is the product, the second is the logo. It changes nothing else in the product.

Keep the place, size and color as the user gives them. Where the user is silent, choose small, and a color that contrasts with the product.

## Answer Structure
Answer in exactly this form: three short lines, then the render prompt between its two tags. The tags are required: without them no image can be rendered. Write nothing after the closing tag.

**Place**: {where on the product}
**Size**: {small, medium or large}
**Color**: {one color}

<<<RENDER_PROMPT>>>
The product from the first image, unchanged, with the logo from the second image printed {size} in {color} on {place}, same angle, same lighting, same background
<<<END_RENDER_PROMPT>>>

---

User Input: {{QUERY}}

Answer with the three lines, then the render prompt between <<<RENDER_PROMPT>>> and <<<END_RENDER_PROMPT>>>.
