<?php

declare(strict_types=1);

namespace Intentio\Command;

use DirectoryIterator;
use Intentio\Cli\Input;
use Intentio\Cli\Output;
use Intentio\Package\Package;
use Intentio\Package\PackageInterface;

final class InitCommand implements CommandInterface
{
    public function __construct(
        private readonly Input $input,
        private readonly array $config
    ) {
    }

    public function execute(): int
    {
        $packageName = $this->input->getArgument(0); // Get the package name from the command line

        if ($packageName) {
            return $this->handleInitWithArgument($packageName);
        } else {
            return $this->handleInitInteractive();
        }
    }

    private function handleInitWithArgument(string $packageName): int
    {
        $packages = $this->getPackages();
        $chosenPackage = null;

        foreach ($packages as $package) {
            if ($package->getName() === $packageName) {
                $chosenPackage = $package;
                break;
            }
        }

        if ($chosenPackage) {
            return $this->initPackage($chosenPackage);
        } else {
            Output::error("Package '" . $packageName . "' not found.");
            return 1;
        }
    }

    private function handleInitInteractive(): int
    {
        $packages = $this->getPackages();

        if (empty($packages)) {
            Output::error("No packages found in the 'packages/' directory.");
            return 1;
        }

        Output::info("Available Packages:");
        foreach ($packages as $i => $package) {
            Output::writeln("  " . ($i + 1) . ". " . $package->getName());
        }

        $choice = $this->askForPackageChoice(count($packages));

        $chosenPackage = $packages[$choice - 1];

        return $this->initPackage($chosenPackage);
    }

    private function askForPackageChoice(int $numberOfPackages): int
    {
        while (true) {
            Output::info("Enter the number of your chosen package:");
            $choice = trim(fgets(STDIN));

            if (!ctype_digit($choice)) {
                Output::error("Invalid choice. Please enter a number.");
                continue;
            }

            $choice = intval($choice);

            if ($choice < 1 || $choice > $numberOfPackages) {
                Output::error("Invalid choice. Please choose a package from the list.");
                continue;
            }

            return $choice; // Return the 1-based index
        }
    }

    private function initPackage(PackageInterface $package): int
    {
        try {
            $result = $package->init();
            if ($result === 0) {
                Output::success("Package '" . $package->getName() . "' initialized successfully to " . $package->getDestinationPath());                // No longer updating config.php active_package as it's been removed

            }
            return $result;
        } catch (\Throwable $e) {
            Output::error("Error initializing package: " . $e->getMessage());
            return 1;
        }
    }

    private function getPackages(): array
    {
        $packagesDir = $_SERVER['PWD'] . '/packages';
        $foundPackages = [];

        if (!is_dir($packagesDir)) {
            return [];
        }

        $iterator = new DirectoryIterator($packagesDir);

        foreach ($iterator as $fileinfo) {
            if ($fileinfo->isDot()) {
                continue;
            }

            if ($fileinfo->isDir()) {
                $packageName = $fileinfo->getFilename();
                $packagePath = $packagesDir . '/' . $packageName;

                if (file_exists($packagePath . '/manifest.md')) {
                    // Assuming the base spaces path is the project's 'spaces' directory
                    $destinationBasePath = $_SERVER['PWD'] . '/' . $this->config['spaces_base_path'];
                    $foundPackages[] = new Package($packageName, $packagePath, $destinationBasePath);
                }
            }
        }
        return $foundPackages;
    }
}

