# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

Special thanks to [Luca Visciola](https://github.com/melasistema) for the original work and ongoing vision.

## [unreleased]

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
