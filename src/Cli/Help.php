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
        Output::writeln("Usage:");
        Output::writeln("  intentio <command> [arguments] [options]");
        Output::writeln("");
        Output::writeln("Available Commands:");
        Output::writeln("  help                 Display help for INTENTIO.");
        Output::writeln("  chat <query>         Interact with the cognitive environment.");
        Output::writeln("                       Example: intentio chat \"What are my core values?\"");
        Output::writeln("  ingest               Process and index the files in a knowledge space.");
        Output::writeln("                       Example: intentio ingest --space=my_private_notes");
        Output::writeln("  interact             Launch a guided interactive mode for conversation.");
        Output::writeln("  status               Display current INTENTIO system status and configuration.");
        Output::writeln("  clear                Remove data (vector store) for a specified cognitive space.");
        Output::writeln("                       Example: intentio clear --space=spaces/my_private_notes");
        Output::writeln("");
        Output::writeln("Options:");
        Output::writeln("  --space=<path>       Specify the path to the knowledge space to use.");
        Output::writeln("                       (Default: configured spaces_base_path)");
        Output::writeln("  --template=<name>    Specify the prompt template to use for chat. (Default: 'default')");
        Output::writeln("");
        Output::writeln("INTENTIO - A local, private, CLI-based cognitive environment.");
        Output::writeln("For more details, refer to the project's documentation.");
        Output::writeln("");
    }
}
