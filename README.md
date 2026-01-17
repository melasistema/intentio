
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

-   ❌ A "chat with your PDFs" demo

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

## Architecture Overview

-   **Language:** PHP (explicit, boring, honest)

-   **LLM:** Local open-source models (via HTTP)

-   **Embeddings:** Local, inspectable, deterministic

-   **Storage:** Local filesystem + vector index


No cloud calls. No silent training. No external APIs.

Your data stays where it belongs.

----------

## Getting Started

To fully utilize INTENTIO, you need to set up a local model server and prepare your knowledge spaces.

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

### 2. Organize Your Knowledge Space

INTENTIO treats your filesystem structure as a cognitive space.

-   Create a main `knowledge/` directory in the project root (if you haven't already).
-   Inside `knowledge/`, create subdirectories for each "cognitive space" you want (e.g., `my_private_notes`, `project_research`).
-   Within each cognitive space directory, you can further organize your documents (e.g., Markdown files, text files) into "cognitive categories" like `reference/`, `memory/`, `opinion/`. The top-level folder within your space determines the category.

    Example structure:
    ```
    knowledge/
    └── my_private_notes/
        ├── reference/
        │   └── article_summary.md
        ├── opinion/
        │   └── my_thoughts.txt
        └── journal/
            └── day_1.md
    ```

### 3. Basic Usage

Once Ollama is running and your knowledge is organized, you can use INTENTIO's commands:

**a. Ingest Your Knowledge:**
   Process your knowledge space to generate embeddings and build the vector store. This must be done for each space you want to use.

   ```bash
   ./intentio ingest --space=knowledge/my_private_notes
   ```
   *Replace `knowledge/my_private_notes` with the path to your specific knowledge space.*

**b. Chat with Your Knowledge:**
   Interact with a specific cognitive space.

   ```bash
   ./intentio chat "What are my core values?" --space=knowledge/my_private_notes
   ```
   *Replace with your query and knowledge space path.*

**c. Interactive Mode (Recommended for exploration):**
   Launch a guided interactive session to easily switch between knowledge spaces and chat.

   ```bash
   ./intentio interact
   ```
   *Follow the on-screen prompts to select a space and chat.*

**d. Get Help:**
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

## Final Note

INTENTIO will not shout. It will not promise enlightenment. It will not pretend to be alive.

It will simply say:

> "Here is a private space. Fill it with meaning. I will respect it."

Sometimes, that is revolutionary enough.