# Hook Analyzer Package

A cognitive instrument for analyzing and improving marketing hooks, grounded in a knowledge base of persuasive techniques and cognitive biases.

This package operates as a suite of "commands" that you can run from the INTENTIO interactive mode. Each command is a prompt template designed for a specific task.

## How It Works

When you select a prompt template (a "command") in interactive mode, you will be given a specific instruction for what to enter next. The system will then analyze your input using its curated knowledge base on hook models, evaluation rules, and cognitive biases.

## Available Commands (Prompts)

*   **`analyze_hook`**
    *   Analyzes a single hook for clarity, attention capture, emotional leverage, and cognitive bias usage.
    *   *Instruction: Enter the hook you want to analyze:*

*   **`improve_hook`**
    *   Provides specific, actionable suggestions to improve an existing hook.
    *   *Instruction: Enter the hook you want to improve:*

*   **`compare_hooks`**
    *   Compares two hooks side-by-side on multiple dimensions and identifies the stronger one.
    *   *Instruction: Enter the two hooks to compare, separated by '||':*

*   **`default`**
    *   Allows you to ask a general question about the knowledge contained within this package (e.g., "What is the anchoring bias?").
    *   *Instruction: Ask a question about the knowledge in this space:*

## Example Usage

1.  Start interactive mode: `./intentio interact`
2.  Select the `hook_analyzer` space.
3.  Select the `analyze_hook` template. The system will then prompt you with instructions:

```
Current Knowledge Space: /path/to/knowledge/hook_analyzer
Current Prompt Template: analyze_hook
Enter the hook you want to analyze:
INTENTIO > Most tools fail due to busyness.
```

The system will then provide a detailed analysis of your hook.