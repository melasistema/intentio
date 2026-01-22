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
    private Input $input;

    public function __construct(Input $input)
    {
        $this->input = $input;
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

        echo "Available Packages:\n";
        foreach ($packages as $i => $package) {
            echo ($i + 1) . ". {$package->getName()}\n";
        }

        $choice = $this->askForPackageChoice(count($packages));

        $chosenPackage = $packages[$choice - 1];

        return $this->initPackage($chosenPackage);
    }

    private function askForPackageChoice(int $numberOfPackages): int
    {
        while (true) {
            echo "Enter the number of your chosen package:\n";
            $choice = trim(fgets(STDIN));

            if (!ctype_digit($choice)) {
                echo "Invalid choice. Please enter a number.\n";
                continue;
            }

            $choice = intval($choice);

            if ($choice < 1 || $choice > $numberOfPackages) {
                echo "Invalid choice. Please choose a package from the list.\n";
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
                // Update config.php with the active package
                $configPath = $_SERVER['PWD'] . '/config.php';
                if (file_exists($configPath) && is_readable($configPath)) {
                    $configContent = file_get_contents($configPath);
                    // Use eval to parse the PHP array. This assumes config.php returns a simple array.
                    // This is generally risky, but acceptable for internal config management.
                    $config = eval('?>' . $configContent);
                    if (is_array($config)) {
                        $config['active_package'] = $package->getName();
                        $newConfigContent = "<?php\n\nreturn " . var_export($config, true) . ";\n";
                        if (file_put_contents($configPath, $newConfigContent) !== false) {
                            Output::info("Updated 'active_package' in config.php to '" . $package->getName() . "'");
                        } else {
                            Output::error("Failed to update 'active_package' in config.php.");
                        }
                    } else {
                        Output::error("config.php does not return a valid array.");
                    }
                } else {
                    Output::info("config.php not found or not readable. Cannot set active package.");
                }
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
                    $destinationBasePath = $_SERVER['PWD'] . '/spaces';
                    $foundPackages[] = new Package($packageName, $packagePath, $destinationBasePath);
                }
            }
        }
        return $foundPackages;
    }
}

