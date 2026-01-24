# Comprehensive Study Report Generator

This generator synthesizes a full learning session by orchestrating the `connection_analyst` and the `exam_generator`.

## Instructions:
1. **Input:** Receive a complex scientific topic or query from the user.
2. **Phase 1: Deep Analysis:** - Call the logic of `connection_analyst.md`.
    - Identify the biological, physical, and chemical intersections.
    - Use {{CONTEXT}} to verify all constants ($F_{stall}$, step-size, etc.).
3. **Phase 2: Pedagogical Alignment:**
    - Scan `memory/past_mistakes.md` to identify specific calculation errors or conceptual gaps.
    - Adjust the explanation in Phase 1 to address these gaps directly.
4. **Phase 3: Assessment Generation:**
    - Call the logic of `exam_generator.md`.
    - Create 3-5 practice questions that directly test the "Connections" found in Phase 1.
5. **Output:** - **Part A:** The Connection Report (The "Why").
    - **Part B:** The Personalized Math Walkthrough (The "How").
    - **Part C:** The Practice Exam (The "Check").