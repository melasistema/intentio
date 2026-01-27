<?php

declare(strict_types=1);

namespace Intentio\Application\Console\Commands;

interface CommandInterface
{
    public function getName(): string;
    public function getDescription(): string;
    public function execute(array $arguments, array $options): int;
}
