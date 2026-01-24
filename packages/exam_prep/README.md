
# 🧠 Second Brain Exam Prep

**The Recursive Learning Package for INTENTIO**

This package transforms INTENTIO into a cross-disciplinary tutor that connects disparate academic subjects into a unified cognitive space. It moves beyond simple summarization by "stitching" together facts from different folders to solve complex problems.

## 📂 Structure

-   **`knowledge/reference/`**: The "Source of Truth." Textbooks and notes organized by subject (Biology, Physics, Chemistry).

-   **`knowledge/values/`**: The "Rules of Engagement." Defines study goals and sets `epistemic_boundaries` to prevent AI hallucinations.

-   **`knowledge/memory/`**: The "Progress Tracker." A private log of your past mistakes and areas where you need more practice.

-   **`prompts/tutor.md`**: The logic engine that forces the AI to cross-reference all folders before answering.


## 🚀 How to Use

1.  **Populate your Knowledge Space**:

    Drop your lecture notes (Markdown) into the respective `reference/` sub-folders.

2.  **Initialize the Package**:

    Run the `init` command and select **Second Brain Exam Prep**.

3.  **Start Interacting**:

    Run `./intentio interact` and select the `tutor` prompt to begin your session.


----------

## 🔍 Case Study: Molecular Energetics

**The Query:**

> _"Using the $F_{stall}$ from my physics notes and the ionic properties of Calcium in my chemistry notes, solve this: If I previously struggled with Work ($W$) calculations in my memory logs, walk me through the energy required for a kinesin motor to move a $Ca^{2+}$ vesicle 100nm."_

**How INTENTIO Solved It:**

1.  **Retrieved Physics:** It pulled the constant $F_{stall} = 7\text{ pN}$ from `kinematics.md`.

2.  **Retrieved Chemistry:** It pulled the energy of ATP ($\approx 90\text{ pN}\cdot\text{nm}$) from the periodic table notes.

3.  **Recursive Math:**

    -   **Work Calculation:** $W = 7\text{ pN} \times 100\text{ nm} = 700\text{ pN}\cdot\text{nm}$.

    -   **Fuel Efficiency:** It calculated that this move requires $\approx 7.7$ ATP molecules.

4.  **Pedagogical Twist:** Because `memory/past_mistakes.md` showed a struggle with Work, the AI provided a first-principles breakdown of $W = F \cdot d$.

5.  **Epistemic Guardrail:** The AI correctly **refused to calculate** the drag force of the fluid because the "Viscosity" constant was missing from the local folders, preventing a macro-scale hallucination.


----------

## 🛡️ Epistemic Safety

This package includes `values/epistemic_boundaries.md`. If a constant (like gravity or mass) is missing from your notes, the AI is **forbidden from guessing**. It will instead state "MISSING DATA" and ask you to provide the local reference, ensuring 100% scientific integrity.