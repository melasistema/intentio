![image README-banner.png](/resources/images/intentio-header-image.png)

# INTENTIO

_A private cognitive environment. Not a chatbot. Not a cloud service. A mindset._

----------

## What is INTENTIO?

INTENTIO is a **local-first, private AI system** designed to operate inside a carefully constructed knowledge space.

It does not try to know everything. It tries to understand **one context well** — the one _you_ design.

INTENTIO is not about artificial intelligence as spectacle. It is about **attention, structure, and restraint**.

----------

## A Different Philosophy

Most AI systems are built as oracles:

-   Global knowledge

-   Generic answers

-   Invisible data flows


INTENTIO rejects that model.

Instead, it treats AI as:

> An interpreter of meaning inside a bounded cognitive space.

This space is:

-   **Private** — nothing leaves your machine

-   **Local** — models and embeddings run on your hardware

-   **Intentional** — structure matters more than scale

-   **Inspectable** — every component can be understood


Private AI is not a feature. It is an architectural decision.

----------

## What INTENTIO Is Not

-   ❌ A chatbot trained on the internet

-   ❌ A "chat with your DOCs" demo

-   ❌ An autonomous agent pretending to think

-   ❌ A cloud-dependent service


INTENTIO does not simulate consciousness. It does not improvise identities. It does not speculate beyond its boundaries.

----------

## What INTENTIO Is

-   ✅ A **cognitive instrument**

-   ✅ A **local retrieval-and-reasoning engine**

-   ✅ A system that values **structure over scale**

-   ✅ A tool for designing _how_ AI pays attention


The same engine can become:

-   A research assistant grounded in primary sources

-   A personal archive that remembers carefully

-   A legal or technical analyst that refuses to speculate

-   A creative collaborator shaped by metaphor, not noise


Not because the model changes — but because the **context does**.

----------

## Core Ideas

### 1. Context Is the Intelligence

A modest local model with excellent context beats a massive model with none.

INTENTIO treats:

-   folders as **signals**

-   metadata as **epistemic boundaries**

-   retrieval as **attention**, not search


You are not organizing files. You are teaching the system _what matters_.

---

### Knowledge Packages: Ready-Made Cognitive Instruments

To make starting with INTENTIO even more effortless, we've introduced **Knowledge Packages**. These are pre-composed, ready-to-use cognitive environments designed with opinionated structures. Think of them as templates or extensions for your INTENTIO experience.

**Why Packages?**
INTENTIO values intentional design, but creating a nuanced cognitive space from scratch can be a cognitive burden. Packages solve this by providing:

-   **Instant Value:** Jump straight into a specialized cognitive task without initial setup.
-   **Opinionated Structure:** Best practices for knowledge organization, prompts, and generators are built-in.
-   **Reduced Overhead:** No need to invent structures or deeply understand orchestration from day one.

**How to Use Packages:**
Discover and activate a package with a single command:

```bash
./intentio init <package_name>
```
Once initialized, the package's specific knowledge, prompts, and generators become available for immediate use.

---

### The Cognitive Space & Categories

A "knowledge root" in INTENTIO is a **designed cognitive space**. The filesystem structure within this space is not arbitrary; it **carries meaning**:

-   **Folders are signals.**
-   **Names matter.**
-   **Depth matters.**

INTENTIO explicitly leverages this structure to define **Canonical Cognitive Categories**. These are conceptual distinctions that help organize knowledge and inform the AI's interpretation:

-   **Memory** — personal, subjective, experiential.
-   **Reference** — factual, external, stable.
-   **Opinion** — interpretive, contextual, non-authoritative.
-   **Value** — normative, guiding principles.

The system is designed to respect these distinctions, allowing for more precise retrieval and tailored AI responses. You are not just storing files; you are shaping the AI's understanding by structuring its cognitive environment.

----------

### 2. Structure Creates Trust

Clear separation between:

-   facts and opinions

-   memory and reference

-   speculation and evidence


This structure produces:

-   stable tone

-   predictable behavior

-   value alignment


Intelligence emerges from constraint.

----------

### 3. Restraint Is a Feature

INTENTIO prefers:

-   fewer documents

-   smaller models

-   limited retrieval

-   explicit boundaries


Because meaning survives only when it is respected.

----------

## Shaping Intent: Designing AI Personas with Prompt Templates

INTENTIO empowers you to go beyond generic AI interactions by designing how your AI "thinks" and "behaves" within a given cognitive space. This is achieved through **configurable prompt templates**, which function as self-contained "commands" for your cognitive environment.

Prompts are not merely instructions; they are fundamental tools for **agent design**. They act as:

-   **Values**: Guiding principles for interpretation.
-   **Boundaries**: Defining the scope of acceptable responses.
-   **Tone Setters**: Influencing the style and persona of the AI.

### How it Works:

1.  **Template Files**: Prompt templates are simple Markdown (`.md`) files located within the `prompts/` directory of your **specific knowledge package** (e.g., `packages/hook_analyzer/prompts/`). Each file defines a distinct "stance" or "command" for the AI.
2.  **Self-Describing Commands**: Each prompt template can include YAML front matter at the top to provide a user-facing `instruction` (e.g., `--- instruction: "Enter the hook you want to analyze:" ---`). This instruction is automatically displayed in interactive mode to guide your input.
3.  **Flexible Design**: These templates allow you to:
    *   Guide the LLM to adopt specific personas (e.g., `analytical`, `creative`, `skeptical`).
    *   Provide task-specific instructions (e.g., summarize, extract facts, generate narratives).
    *   Enforce strict grounding rules, ensuring responses adhere solely to the provided context.
4.  **Placeholders**: Each template uses `{{CONTEXT}}` to inject retrieved knowledge and `{{QUERY}}` for the user's question, allowing you to craft precise instructions around this core information.
5.  **Usage**: Select your desired template using the `--template=<name>` option with the `chat` command, or dynamically switch personas within the `interact` command. The system will provide clear guidance on what input is expected.

By leveraging configurable prompt templates, you transform INTENTIO into a truly adaptable cognitive instrument, capable of adopting diverse "cognitive stances" to match your specific needs and intentions.

----------

## Architecture Overview

-   **Language:** PHP (explicit, boring, honest)

-   **LLM:** Local open-source models (via HTTP)

-   **Embeddings:** Local, inspectable, deterministic

-   **Storage:** Local SQLite database (vector index)


No cloud calls. No silent training. No external APIs.

Your data stays where it belongs.

----------

## Getting Started

To fully utilize INTENTIO, you need to set up a local model server and prepare your cognitive environment.

### 1. Prerequisites: Install Ollama and Download Models

INTENTIO uses [Ollama](https://ollama.com) to run local Large Language Models (LLMs) and embedding models.

**a. Install Ollama:**
   - Go to [https://ollama.com](https://ollama.com) and download the application for your operating system.
   - Install it as you would any other application. The Ollama server typically runs in the background automatically.

**b. Download Required Models:**
   - Open your terminal and pull the necessary models:
     ```bash
     ollama pull nomic-embed-text
     ollama pull llama3.1
     ```
   - Verify installation: `ollama list` should show `nomic-embed-text:latest` and `llama3.1:latest`.

### 2. Initialize a Knowledge Package (Recommended First Step)

Start with a pre-configured cognitive environment. This is the quickest way to experience INTENTIO's capabilities.

```bash
./intentio init hook_analyzer
# Or choose from available packages interactively:
# ./intentio init
```
This command will deploy the `hook_analyzer` package, setting up its knowledge space, specialized prompts (like `analyze_hook`), and generators in your INTENTIO environment. The package you initialize will become your `active_package`.

### 3. Organize Your Custom Knowledge Space (Optional, for Advanced Users)

While packages provide ready-made structures, you can still create and manage your own custom cognitive spaces.

INTENTIO treats your filesystem structure as a cognitive space.

-   Create a main `knowledge/` directory in the project root (if you haven't already).
-   Inside `knowledge/`, create subdirectories for each "cognitive space" you want (e.g., `my_private_notes`, `project_research`).
-   Within each cognitive space directory, you can further organize your documents (e.g., Markdown files, text files) into "cognitive categories" like `reference/`, `memory/`, `opinion/`. The top-level folder within your space determines the category.
-   **If you want to define custom commands (prompts) for your space, create a `prompts/` subdirectory within your space's root (e.g., `knowledge/my_private_notes/prompts/`) and add your `.md` prompt files there.**

    Example structure for a custom space:
    ```
    knowledge/
    └── my_private_notes/
        ├── knowledge/        # All files here are ingested for RAG
        │   ├── reference/
        │   │   └── article_summary.md
        │   ├── opinion/
        │   │   └── my_thoughts.txt
        │   └── journal/
        │       └── day_1.md
        └── prompts/          # Your custom commands (prompts) for this space
            └── my_question_answering.md
    ```

### 4. Basic Usage

Once Ollama is running and your knowledge environment (either package-initialized or custom) is ready, you can use INTENTIO's commands:

**a. Ingest Your Knowledge (for Custom Spaces or after package updates):**
   Process your knowledge space to generate embeddings and build its SQLite-based vector store. This must be done for each space you want to use. **Only content within the `knowledge/` subdirectory of your space will be ingested.**

   ```bash
   ./intentio ingest --space=knowledge/my_private_notes
   # Or for a package-initialized space:
   # ./intentio ingest --space=knowledge/hook_analyzer
   ```
   *Replace `knowledge/my_private_notes` with the path to your specific knowledge space.*

**b. Chat with Your Knowledge:**
   Interact with a specific cognitive space, choosing a prompt template (command) defined within that space.

   ```bash
   ./intentio chat "Analyze this hook: 'Most tools fail due to busyness.'" --space=knowledge/hook_analyzer --template=analyze_hook
   ```
   *Replace with your query, knowledge space path, and the name of a template available in that space (e.g., `analyze_hook`, `default`).*

**c. Interactive Mode (Recommended for exploration and guided experience):**
   Launch a guided interactive session. Here you can easily switch between knowledge spaces, select prompt templates (commands), and chat. The system will intelligently detect uningested or outdated spaces and offer to ingest/re-ingest them. All prompt templates are loaded exclusively from the `prompts/` directory of the currently active knowledge package/space.

   ```bash
   ./intentio interact
   ```
   *Follow the on-screen prompts to select a space, template, and chat.*

**d. Clear a Cognitive Space's Data:**
   Remove the SQLite vector store for a specified cognitive space. This is useful for starting fresh or if you've significantly restructured your source files and want a full re-ingestion.

   ```bash
   ./intentio clear --space=knowledge/my_private_notes
   ```
   *Replace `knowledge/my_private_notes` with the path to the space you wish to clear.*

**e. Get System Status:**
   View an overview of your INTENTIO setup, including configured paths, available knowledge spaces, and Ollama server/model status.

   ```bash
   ./intentio status
   ```

**f. Get General Help:**
   ```bash
   ./intentio help
   ```

----------

## How It Works (Conceptually)

1.  You design a knowledge space

2.  Documents are ingested with meaning-aware structure

3.  Text is embedded locally

4.  Retrieval selects _relevant attention_, not everything

5.  The model responds **only within provided context**


If something is not in the space, INTENTIO does not pretend it knows.

----------

## This Is Not a Revolution

We are not inventing intelligence. We are not defeating Big Tech. We are not chasing benchmarks.

We are quietly reframing the question:

> "In what kind of cognitive space do we want AI to operate?"

That shift is small. But it changes everything.

----------

## Design Principles

-   Privacy over convenience

-   Explicit over magical

-   Local over global

-   Meaning over performance

-   Restraint over spectacle


----------

## Status

INTENTIO is under active development.

This repository represents:

-   an experiment in cognitive design

-   a refusal of unnecessary complexity

-   a belief that AI should adapt to humans — not the opposite


----------

## Credits

INTENTIO is an open-source project created and maintained by [Luca Visciola](https://github.com/melasistema).

----------

## Final Note

INTENTIO will not shout. It will not promise enlightenment. It will not pretend to be alive.

It will simply say:

> "Here is a private space. Fill it with meaning. I will respect it."

Sometimes, that is revolutionary enough.