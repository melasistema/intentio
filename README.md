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
-   A creative collaborator for platform-aware content generation, shaped by context, not noise
-   A marketing strategist for multi-platform hook analysis and comprehensive reports
-   A **visual art director**, consistently generating images in a defined style, like the `Cartoon Universe` blueprint for animated content.


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
-   **Highly Specialized Instruments:** Provide the structure for building advanced tools like platform-specific content analysis or multi-faceted reporting.
-   **Opinionated Structure:** Best practices for knowledge organization, prompts, and generators are built-in.
-   **Reduced Overhead:** No need to invent structures or deeply understand orchestration from day one.

**How to Use Packages:**
Discover and activate a package with a single command:

```bash
./intentio init <package_name>
# Or choose from available packages interactively:
# ./intentio init
```
This command will deploy the chosen package, setting up its knowledge space, specialized prompts, and generators in your INTENTIO environment.

**Browse Available Packages:**
Explore the specific README files for each pre-built package to understand its purpose, internal structure, and usage examples:

-   [Hook Analyzer](packages/hook_analyzer/README.md)
-   [Cartoon Universe](packages/cartoon_universe/README.md)

---

### The Cognitive Space Structure

A cognitive space in INTENTIO is a **designed, filesystem-driven environment**. Its structure is not arbitrary; it **carries meaning**:

-   **Folders are signals.**
-   **Names matter.**
-   **Depth matters.**

Within each space, two primary top-level directories are recognized by INTENTIO:

-   **`knowledge/`** — This is the root for all your content that will be ingested by the RAG system. You, as the cognitive designer, are free to organize this folder with any subfolder structure (e.g., `knowledge/reference/`, `knowledge/memory/`, `knowledge/domain_specific_data/`). INTENTIO will ingest all supported files (`.md`, `.txt`) found within `knowledge/` and its subdirectories. The subfolder names can be used by prompt templates to provide contextual instructions to the AI.
-   **`prompts/`** — This fixed directory is where all your prompt templates (`.md` files) for the cognitive space reside. These templates define the "commands" and behaviors of the AI within this space.

INTENTIO explicitly leverages this structure. By organizing your `knowledge/` folder intentionally, you are shaping the AI's understanding and guiding its responses. You are not just storing files; you are designing its cognitive environment.

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
5.  **Usage**:
    *   **Default Prompt:** You can define a global default prompt template name in your `config/app.php` file.
    *   **Package Default:** A `default_prompt` can be specified in a space's `manifest.md` to override the global default for that specific space.
    *   **Command Line:** Use the `--prompt-key=<name>` option with the `chat` command for a one-off prompt selection.
    *   **Interactive Mode:** Within the `./intentio interact` session, you will be prompted to select an initial prompt template, and you can dynamically switch between templates using the `switch_prompt` command. The `instruction` from the template's front-matter will guide your input.

By leveraging configurable prompt templates, you transform INTENTIO into a truly adaptable cognitive instrument, capable of adopting diverse "cognitive stances" to match your specific needs and intentions.

----------

## Architecture Overview

-   **Language:** PHP (explicit, boring, honest)

-   **LLM:** Local open-source models (via HTTP)

-   **Embeddings:** Local, inspectable, deterministic

-   **Storage:** Local SQLite database (vector index)

-   **Image Renderer:** Local Image Generation Models (via Ollama)


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
     ollama pull x/z-image-turbo
     ```
   - Verify installation: `ollama list` should show `nomic-embed-text:latest` and `llama3.1:latest`.

### 2. Configure INTENTIO

INTENTIO uses a configuration file located at `config/app.php` for its core settings.

-   Review the provided `config/app.php` file. You may need to adjust Ollama server details, default model names, or paths to match your local setup.
-   For local overrides, you can create a `config/app.local.php` file. This file will be loaded *after* `config/app.php` and its values will override existing ones. `config/app.local.php` is ignored by version control.

### 3. Initialize a Knowledge Package (Recommended First Step)

Start with a pre-configured cognitive environment. This is the quickest way to experience INTENTIO's capabilities.

```bash
./intentio init hook_analyzer
# Or choose from available packages interactively:
# ./intentio init
```
This command will deploy the `hook_analyzer` package, setting up its knowledge space, specialized prompts (like `analyze_hook`), and generators in your INTENTIO environment. The package you initialize will become your `active_package`.

### 3. Organize Your Custom Cognitive Space (Optional, for Advanced Users)

While packages provide ready-made structures, you can still create and manage your own custom cognitive spaces from scratch.

INTENTIO treats your filesystem structure as a cognitive space.

-   Create a main `spaces/` directory in the project root (if you haven't already).
-   Inside `spaces/`, create a subdirectory for each "cognitive space" you want (e.g., `my_private_notes`, `project_research`).
-   Within each cognitive space directory, INTENTIO expects the following structure:
    -   **`knowledge/`**: This directory is the heart of your RAG system. Organize all your Markdown (`.md`) and text (`.txt`) files here. You can create any subfolder structure you desire (e.g., `knowledge/reference/`, `knowledge/memory/`, `knowledge/specific_project_data/`). INTENTIO will recursively scan and ingest all supported files from this directory and its subfolders.
    -   **`prompts/`**: This fixed directory is where you place all your custom prompt templates (`.md` files) for this specific space. Each `.md` file represents a distinct AI persona or command.

    Example structure for a custom space (`spaces/my_private_notes/`):
    ```
    spaces/
    └── my_private_notes/
        ├── knowledge/              # Root for all RAG content
        │   ├── reference/
        │   │   └── article_summary.md
        │   ├── memory/
        │   │   └── personal_insights.md
        │   └── project_data/
        │       └── meeting_notes.txt
        └── prompts/                # Your custom commands/prompt templates
            ├── default.md
            └── summarize_doc.md
    ```

### 4. Basic Usage

Once Ollama is running and your knowledge environment (either package-initialized or custom) is ready, you can use INTENTIO's commands:

**a. Ingest Your Knowledge (for Custom Spaces or after package updates):**
   Process your cognitive space to generate embeddings and build its SQLite-based vector store. This must be done for each space you want to use. **All supported files (`.md`, `.txt`) within the `knowledge/` and `prompts/` subdirectories of your space will be ingested.**

   ```bash
   ./intentio ingest --space=my_private_notes
   # Or for a package-initialized space:
   # ./intentio ingest --space=hook_analyzer
   ```
   *Replace `my_private_notes` with the name of your specific cognitive space. The system will look for it under your configured `spaces_base_path`.*

**b. Chat with Your Knowledge:**
   Interact with a specific cognitive space, choosing a prompt template (command) defined within that space.

   ```bash
   ./intentio chat "Analyze this hook: 'Most tools fail due to busyness.'" --space=hook_analyzer --prompt-key=analyze_hook
   ```
   *Replace `hook_analyzer` with the name of your cognitive space, and `analyze_hook` with the name of a prompt template available in that space.*

**c. Interactive Mode (Recommended for exploration and guided experience):**
   Launch a guided interactive session. Here you can easily switch between knowledge spaces, select prompt templates (commands), and chat. The system will intelligently detect uningested or outdated spaces and offer to ingest/re-ingest them.

   ```bash
   ./intentio interact
   ```
   *Follow the on-screen prompts to select a space and initial prompt template. Once in the session, type `switch_prompt` to change the active prompt template.*

**d. Clear a Cognitive Space's Data:**
   Remove the SQLite vector store for a specified cognitive space. This is useful for starting fresh or if you've significantly restructured your source files and want a full re-ingestion.

   ```bash
   ./intentio clear --space=my_private_notes
   ```
   *Replace `my_private_notes` with the name of the space you wish to clear.*

**e. Generate an Image:**
   After generating a manifest (e.g., using a blueprint like `cartoon_universe`) in interactive mode, you can render an image.

   ```bash
   ./intentio interact
   # Follow prompts to select a space (e.g., 'cartoon_universe') and a prompt that generates a manifest.
   # Once a manifest is generated, the system will prompt you to "Render this manifest? (yes/no/refine):"
   # Type 'yes' to generate the image.
   ```
   *The generated image will be saved to the space's output directory.*

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