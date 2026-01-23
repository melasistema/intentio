<?php

declare(strict_types=1);

namespace Intentio\Cli;

/**
 * Provides helpful information about INTENTIO commands and usage.
 *
 * Adheres to the principle of making the system discoverable without
 * extensive external documentation.
 */
final class Help
{
    public static function display(): void
    {
        Output::writeln("");
        Output::info("Usage:");
        Output::info("  intentio <command> [arguments] [options]");
        Output::writeln("");
        Output::info("Available Commands:");
        Output::info("  help                 Display help for INTENTIO.");
        Output::info("  chat <query>         Interact with the cognitive environment.");
        Output::info("                       Example: intentio chat \"What are my core values?\"");
        Output::info("  ingest               Process and index the files in a cognitive space.");
        Output::info("                       Example: intentio ingest --space=my_private_notes");
        Output::info("  interact             Launch a guided interactive mode for conversation.");
        Output::info("  status               Display current INTENTIO system status and configuration.");
        Output::info("  clear                Remove data (vector store) for a specified cognitive space.");
        Output::info("                       Example: intentio clear --space=spaces/my_private_notes");
        Output::writeln("");
        Output::info("Options:");
        Output::info("  --space=<path>       Specify the path to the cognitive space to use.");
        Output::info("                       (Default: configured spaces_base_path)");
        Output::info("  --template=<name>    Specify the prompt template to use for chat. (Default: 'default')");
        Output::writeln("");
        Output::info("INTENTIO - A local, private, CLI-based cognitive environment.");
        Output::info("For more details, refer to the project's documentation.");
        Output::writeln("");
    }
}
