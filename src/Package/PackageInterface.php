<?php

namespace Intentio\Package;

interface PackageInterface
{
    public function init(): int;
    public function getDestinationPath(): string;
}