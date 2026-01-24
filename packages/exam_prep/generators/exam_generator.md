# Exam Generator

This generator is designed to transform scientific context into a structured, pedagogical assessment.

## Instructions:
1. **Input:** Receive a topic or specific scientific query from the user via {{QUERY}}.
2. **Context:** Utilize {{CONTEXT}}, specifically looking for facts in `reference/` and constraints in `values/study_goals.md`.
3. **Process:**
    * **Calibration:** Check the difficulty level in `study_goals.md`.
    * **Question Design:** Create questions that require the student to apply formulas from `physics/` to biological scenarios from `biology/`.
    * **Personalization:** Include at least one question targeting a weakness found in `memory/past_mistakes.md`.
4. **Output:** Provide a structured exam (Multiple Choice, Short Answer, and Problem Solving). Do not provide answers immediately.