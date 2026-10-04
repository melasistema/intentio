# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

Special thanks to [Luca Visciola](https://github.com/melasistema) for the original work and ongoing vision.

## [unreleased]

### Added
-   **Product Pitch Lab Blueprint:** Introduced the `@packages/product_pitch_lab` cognitive environment (`version: 0.1.0`), which validates product ideas against business frameworks, drafts elevator pitches, competitive analyses and Lean Canvases, and renders product mockups, logo concepts and landing page designs.
-   **Sources Under Each Answer:** `chat` and `interact` list what an answer was built from: the knowledge files pinned by the prompt template and the retrieved passages, each with its similarity score. When nothing was used, the list says so.
-   **Retrieval Settings:** `retrieval.limit` and `retrieval.min_score` in `config/app.php`.
-   **Embedding Prefixes:** `embedding.document_prefix` and `embedding.query_prefix` put the words an embedding model expects in front of knowledge sections and queries (`search_document: ` and `search_query: ` for the default model).
-   **Context Window Warning:** A warning is printed when a prompt is estimated to be larger than the model's context window (`llm.context_window`, 32768 by default).
-   **Batch Embedding:** The sections of a knowledge file are embedded in one request.
-   **Streamed Answers:** `chat` and `interact` show an answer while the model writes it, instead of after its last word. `omlx.timeout` is now the longest silence accepted from the server, not the time allowed for a whole answer.
-   **Tests:** `composer test` runs PHPUnit tests of the chunker, the prompt front matter, the prompt assembly and the similarity search. They need no model and no server. PHPUnit is a development dependency only.
-   **`status` Command:** Lists the models on the oMLX server and reports a configured model that is missing.

### Changed
-   **oMLX Instead of Ollama (configuration change):** The language model and the embedding model are now served by a local [oMLX](https://github.com/jundot/omlx) server through one client. The `ollama` configuration block is replaced by `omlx` (`base_url`, `api_key`, `timeout`); `llm.model_name` and `embedding.model_name` are the names oMLX lists; `llm.options` holds sampling options only, and `num_ctx` and `keep_alive` are gone. The default models are `Mistral-7B-Instruct-v0.3-4bit` and `nomicai-modernbert-embed-base-bf16`. Spaces are re-indexed on their next ingestion, because the embedding model changed.
-   **mflux Instead of Ollama for Images (configuration change):** Images are rendered by the local [mflux](https://github.com/filipstrand/mflux) command line tool, because Ollama no longer runs image models. `image_renderer` now holds the mflux `command`, the `model_name` and the `options` passed to it; the default model is `mflux-community/flux2-klein-4b-mflux-q4`. The rendering progress is shown while the image is made, and images are named `render_<date>-<time>.png`.
-   **Rendering Declared by the Prompt (package format change):** A prompt template whose answers are rendered now says `render: true` in its front matter. The `actions` block of `manifest.md` is no longer read, and `default_prompt` is the only manifest line the engine uses. Spaces created earlier need `render: true` added to their rendering prompts. The answer is no longer saved to `lastGeneratedManifest.md`; the image prompt is taken from the answer itself.
-   **Active Prompt Template:** In `interact`, the selected prompt template stays active until `switch_prompt` is used, and is shown in front of each query. Previously a template had to be selected again after every answer.
-   **Errors During a Session:** In `interact`, a failed answer or render is reported and the session continues. A session whose input ends closes without an error.
-   **Minimum Similarity:** A passage is retrieved only if its similarity to the query reaches `retrieval.min_score` (0.32 by default, measured with the default embedding model). Previously the five nearest passages were always sent, however distant.
-   **Pinned Files:** Passages of a file that the prompt template already loads in full are no longer retrieved a second time.
-   **Quiet Output:** Removed debug lines and the engine's internal progress messages from `interact`, `chat`, `ingest`, `clear` and `render`.
-   **`init` Without `--space`:** `./intentio init <blueprint>` now asks for the space name instead of failing.
-   **Missing Prompts:** A prompt template that does not exist is reported as an error instead of silently falling back to `default`.
-   **`render` Command:** Removed the unused prompt resolution; the command renders the given query directly.
-   **Repeatable Ingestion:** `ingest` now compares each knowledge file with the index and embeds only new and modified files; deleted files are removed. Running it twice no longer duplicates chunks, and `clear` is no longer needed after editing knowledge. Changing the embedding model marks every file as modified.
-   **Chunking by Heading:** Knowledge files are now split at their Markdown headings instead of at blank lines. A chunk is a heading with the text under it, it remembers the headings above it, and a heading with nothing under it is no longer a chunk. Files without headings are still split into paragraphs. Retrieved passages are labelled with their file and heading path (e.g., `hook_models.md > Hook Models > The PAS Model`). Existing spaces are re-indexed on their next ingestion.
-   **Knowledge Only:** Prompt templates are no longer indexed, so they can no longer be retrieved as context.
-   **Index Storage:** The index is stored at `.intentio_store/index.sqlite` inside the space, with relative paths and compact vectors. Existing spaces are re-ingested on their next use; `clear` removes the index file left by earlier versions.
-   **Outdated Spaces:** `interact` asks whether to update the index when the knowledge has changed, and `chat` reports a space that was never ingested instead of answering without context.
-   **Prompt Assembly:** The prompt sent to the model is now built in one place: the knowledge in scope, then the template with the query. The `instruction` in a template's front matter is shown on screen only and is no longer sent to the model.
-   **`--prompt-key` Option:** `interact` now reads `--prompt-key` (previously `--prompt_key`), matching `chat`.

### Fixed
-   **Incomplete Index:** A file whose embedding fails is left out of the index as a whole and reported, and `ingest` exits with an error; previously a partial index was treated as complete.
-   **`chat` Command:** The query is now sent to the model. Previously only the retrieved context was sent.
-   **`{{CONTEXT}}` Placeholder:** A template can now place the knowledge in scope with `{{CONTEXT}}`, as documented. Previously the placeholder was left in the prompt as literal text.
-   **`chat --prompt-key`:** The `chat` command now applies the named prompt template, as documented.
-   **Prompt Front Matter:** Front matter with more than one key is now parsed, instead of being sent to the model as part of the prompt.
-   **Ollama Errors:** Error responses from Ollama (e.g., a model that is not pulled) are reported with Ollama's own message, and an empty model response is reported as an error.
-   **Space Names:** Space names given through `--space` are validated, so a space can no longer point outside the spaces directory.
-   **Version:** `./intentio help` now shows the configured application version.

## [0.2.1] - 2026-01-31

### Added
-   **Image Generation Capability:** Introduced `OllamaImageRenderer.php` to leverage local image generation models, enabling coherent visual rendering.
-   **Rendering Workflow:** Introduced a streamlined image rendering workflow, allowing manifest generation and subsequent image creation in interactive sessions.
-   **Cartoon Universe Blueprint:** Introduced the `@packages/cartoon_universe` cognitive environment, a blueprint package demonstrating INTENTIO's ability to deliver coherent and consistent visual rendering in a fun, cartoon style.

### Removed
-   **"Refine" Interaction:** Eliminated the 'refine' option from interactive manifest processing (including removal from manifest files) to enhance stability and ensure predictable LLM behavior.

### Changed
-   **Interactive Context Management:** Implemented forced prompt re-selection after *every* LLM response within interactive sessions, ensuring a clean operational context for subsequent queries and preventing unintended LLM state accumulation.

## [0.2.0] - 2026-01-28

### Added
-   **System Status Command:** Re-introduced `status` command (`./intentio status`) to display comprehensive system, configuration, and Ollama server/model information for enhanced inspectability.

### Changed
-   **Architectural Overhaul (CLI-First, Domain-Driven Refactor):**
    -   Complete refactoring to a layered, domain-driven architecture (`Application`, `Domain`, `Infrastructure`, `Shared`).
    -   Introduction of explicit `Space` and `Blueprint` domain objects, managed by dedicated repositories and factories.
    -   Centralization of core cognitive logic within a `CognitiveEngine` orchestrating `IngestionService`, `RetrievalService`, and `PromptResolver`.
    -   Transition from procedural bootstrapping and static helper classes to dependency injection and formal interfaces for core services.
    -   Flexible Configuration: Replaced `config.php` with `config/app.php` and support for `config/app.local.php` overrides, and updated `intentio` executable to load config from `config/app.php`.
    -   Enhanced CLI Commands: `InteractCommand` now features dynamic space/prompt selection, manifest-driven configuration, and automatic ingestion.
    -   Flexible Cognitive Space Structure: Moved from fixed subdirectories (e.g., `reference/`, `memory/`) to a flexible `knowledge/` root where users define subfolders, while `prompts/` remains fixed.
    -   Ollama Integration: `LocalEmbeddingAdapter` and `OllamaAdapter` now directly interact with Ollama API for embeddings and LLM generation, using configurable models and API paths.
    -   Vector Storage: Implemented `SQLiteVectorStore` to manage space-specific vector indexes.
    -   Updated `README.md` to reflect all architectural changes, new command usages, and the flexible cognitive space structure.

### Removed
-   **Legacy Codebase:** Eliminated old `src/` directories (Command, Cli, Embedding, Ingestion, Knowledge, Orchestration, Package, Storage) and their contents, along with outdated procedural patterns, global state, and direct filesystem dependencies from core logic.

### Fixed
-   **Syntax Errors:** Resolved parser errors caused by incorrect namespace separators.
-   **Typo in 'switch_prompt':** Corrected handling of 'switch_prompt' in interactive mode.

## [0.1.5] - 2026-01-22

### Added
-   **Multi-Platform Enhancements (`hook_analyzer`):**
    -   Introduced platform-specific knowledge assets (e.g., `platform_specs.md`, `viral_patterns.md`) for platforms like LinkedIn and Instagram.
    -   Added `platform_adapter.md` generator blueprint for platform-aware analysis.
    -   New `multi_platform_report.md` prompt for generating comprehensive, platform-tailored hook reports.

## [0.1.4] - 2026-01-24

### Removed
-   **ASCII Welcome**
    -   Removed ASCII welcome.
    -   Replaced with simple text welcome message in `InitCommand.php`.
    -   Simplifies code and improves readability.
    -   Enhances user experience with cleaner output.

## [0.1.3] - 2026-01-24

### Added
-   **New Package – `exam_prep`:**
    -   Added *Second Brain Exam Prep* (`version: 0.1`) under `packages/exam_prep`.
    -   Enables cross-disciplinary exam generation across Biology, Physics, and Chemistry.
    -   Includes recommended generators: `exam_generator`, `connection_analyst`.
    -   Default prompt set to `tutor`.

## [0.1.2] - 2026-01-22

### Added
-   **Prompt Instructions:** Implemented support for `instruction` metadata in YAML front matter of prompt `.md` files, providing guided input for interactive mode.
-   **Comprehensive Report Prompt:** Added `report.md` prompt to `packages/hook_analyzer`, enabling multi-step analysis and improvement suggestions in a single command.
-   **CLI Color Output:** Integrated color support into `Output.php` and across all core CLI commands (`InitCommand`, `IngestCommand`, `ChatCommand`, `InteractCommand`, `ClearCommand`, `StatusCommand`, `Help`) for improved readability and user feedback.

### Changed
-   **Package-Centric Prompts:** Reworked prompt resolution to exclusively load templates from the active package's `prompts/` directory, removing global prompt fallbacks.
-   **`README.md`:** Significantly updated to reflect the new `spaces/` directory, package-centric prompt architecture, `config.example.php` setup, and the guided interactive experience.
-   **`packages/hook_analyzer/README.md`:** Updated to document the new `report` prompt and reflect the `spaces/` directory rename.
-   **`packages/hook_analyzer/prompts/*.md`:** All prompts in the `hook_analyzer` package (`analyze_hook`, `compare_hooks`, `default`, `improve_hook`, `report`) updated with `instruction` front matter.
-   **`config.example.php`:**
    -   Renamed `knowledge_base_path` to `spaces_base_path`.
    -   Removed `prompt_templates_path`.
    -   Removed `active_package` configuration entry.
-   **`InitCommand.php`:**
    -   Constructor now receives `config` array.
    -   `getPackages()` method uses `spaces_base_path` from config.
    -   Removed logic for updating `active_package` in `config.php`.
-   **`Prompt.php`:**
    -   Refactored to be immutable (`readonly` properties).
    -   Introduced `Prompt::fromTemplateFile()` static factory method for loading and parsing prompt files, handling front matter.
-   **`InteractCommand.php`:**
    -   Updated UI/state to default to no template selected.
    -   Removed "show global prompts" option from menu.
    -   Logic added to display `instruction` for selected template.
    -   `chat()` method now validates template selection before execution.
    -   Updated output calls to use new color methods.
-   **`Space.php`:** `scan()` method now exclusively targets the `knowledge/` subdirectory within a cognitive space for ingestion.
-   **`NomicEmbedder.php`:** Added robust defensive checks for `ollamaConfig` keys (`base_url`, `api_path_embeddings`) and URL scheme validation.
-   **Codebase References:** Updated all references from `knowledge/` to `spaces/` (e.g., in `src/Kernel.php`, `src/Command/InteractCommand.php`, `src/Cli/Help.php`).
-   **Docblocks & Help Messages:** Updated terminology from "knowledge space" to "cognitive space" for consistency.

### Removed
-   **Root `prompts/` directory:** All global prompt templates were removed in favor of package-specific prompts.
-   **`active_package` config entry:** Removed as it no longer aligns with the explicit state management.
-   **`prompt_templates_path` config entry:** Removed as global prompts are no longer supported.

### Fixed
-   **`InitCommand` Variable Scope:** Resolved `Undefined variable $chosenPackage` error in `InitCommand::initPackage()`.
-   **`InitCommand` Duplicate Success Message:** Fixed `init` command printing package initialization success message twice.
-   **`Package::getDestinationPath()`:** Implemented missing `getDestinationPath()` method in `Package` class and `PackageInterface`.
-   **`InteractCommand` Instruction Display:** Resolved bug preventing prompt `instruction` from displaying due to `readonly` property issue.
-   **`IngestCommand` Path Handling:** Addressed `str_starts_with(false, ...)` error by clarifying expected `--space` path (user error) and adding robust checks in `NomicEmbedder`.

### Breaking Changes
-   **Directory Rename:** The root `knowledge/` directory has been renamed to `spaces/`. Users must update their local directory structure and any scripts referencing the old path.
-   **Prompt Location:** Global prompt templates are no longer supported. All prompts must now reside within a `prompts/` subdirectory inside their respective cognitive space (e.g., `spaces/my-space/prompts/`).
-   **Configuration Variable Renames:**
    -   `knowledge_base_path` in `config.php` has been renamed to `spaces_base_path`.
    -   `prompt_templates_path` in `config.php` has been removed.
    -   `active_package` in `config.php` has been removed.
    Users must update their `config.php` accordingly.
-   **Command Usage:** Commands (e.g., `chat`, `ingest`, `clear`) relying on `--space` now require the full path, including the `spaces/` base directory (e.g., `--space=spaces/my_private_notes`).
