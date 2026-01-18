<?php

declare(strict_types=1);

namespace Intentio\Command;

/**
 * Interface for all CLI commands in INTENTIO.
 *
 * Ensures that all commands have an executable entry point.
 */
interface CommandInterface
{
    /**
     * Executes the command logic.
     *
     * @return int An exit code (0 for success, non-zero for failure).
     */
    public function execute(): int;
}
