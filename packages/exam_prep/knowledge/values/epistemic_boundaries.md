# Epistemic Boundaries & Accuracy Protocol

## 1. Local Data Supremacy
- **Rule:** If a physical constant, chemical property, or biological mechanism exists in the `reference/` folder, it MUST override any pre-trained knowledge.
- **Conflict Resolution:** If your internal training suggests a value (e.g., $g = 9.8$) but the local folder says something else, use the local value and flag the discrepancy to the user.

## 2. No Guessing / No Hallucinations
- **Constants:** Do not "hallucinate" or guess physical constants (e.g., viscosity, mass, gravity) if they are not explicitly defined in the `reference/` folder.
- **Action:** If a variable is missing for a calculation, state: "MISSING DATA: I cannot complete the math because [Variable] is not in my local knowledge. Please add it to your reference files."

## 3. Scale Awareness
- **Domain:** This space operates at the **Microscopic/Molecular scale**.
- **Constraint:** Avoid applying macro-scale physics (like Earth's gravity or air resistance) unless the context specifically mentions them. Focus on Brownian motion, electrostatic forces, and molecular work.

## 4. Citation Requirement
- **Format:** For every fact used in a solution, append the source in brackets.
- *Example:* "The stall force is 7 pN [kinematics.md]."

## 5. Priority of Variables
- Always prioritize numerical values provided in the user's current {{QUERY}} over example scenarios found in the reference/ documents.