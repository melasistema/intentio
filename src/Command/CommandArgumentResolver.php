<?php

declare(strict_types=1);

namespace Intentio\Command;

use Intentio\Cli\Input;
use Intentio\Cli\Output;
use Intentio\Knowledge\Space;
use ReflectionClass;
use ReflectionNamedType;

/**
 * Resolves and prepares arguments for Command class constructors.
 *
 * This class inspects a Command's constructor and, based on its
 * type hints, attempts to provide the necessary dependencies
 * (Input, Config, Knowledge\Space, knowledgeBasePath).
 * It centralizes dependency resolution for commands.
 */
final class CommandArgumentResolver
{
    public function __construct(
        private readonly Input $input,
        private readonly array $config
    ) {
    }

    /**
     * Resolves the arguments for a given Command class constructor.
     *
     * @param string $commandClass The fully qualified class name of the command.
     * @param array $commandConfig Specific config related to the command (e.g., knowledgeBasePath).
     * @return array An associative array of arguments ready to be passed to the constructor.
     * @throws \RuntimeException If a required dependency cannot be resolved.
     */
    public function resolve(string $commandClass, array $commandConfig): array
    {
        $reflectionClass = new ReflectionClass($commandClass);
        $constructor = $reflectionClass->getConstructor();

        if ($constructor === null) {
            return []; // No constructor, no arguments needed.
        }

        $args = [];
        foreach ($constructor->getParameters() as $parameter) {
            $parameterName = $parameter->getName();
            $parameterType = $parameter->getType();

            // Handle typed parameters
            if ($parameterType instanceof ReflectionNamedType) {
                $typeName = $parameterType->getName();

                switch ($typeName) {
                    case Input::class:
                        $args[$parameterName] = $this->input;
                        break;
                    case 'array': // For config
                        if ($parameterName === 'config') {
                            $args[$parameterName] = $this->config;
                        } elseif ($parameterName === 'commandConfig') { // If a command needs its specific config
                             $args[$parameterName] = $commandConfig;
                        } else {
                            // Handle other array types if necessary, or throw error
                            if ($parameter->isDefaultValueAvailable()) {
                                $args[$parameterName] = $parameter->getDefaultValue();
                            } else {
                                throw new \RuntimeException("Cannot resolve array type for parameter '{$parameterName}' in '{$commandClass}'.");
                            }
                        }
                        break;
                    case Space::class:
                        // This assumes knowledgeSpace is handled outside if not directly resolvable from config
                        // For chat/ingest/clear, Kernel will ensure this is available if needed.
                        // Here, we just retrieve it from commandConfig if it's there.
                        if (isset($commandConfig['knowledgeSpace'])) {
                            $args[$parameterName] = $commandConfig['knowledgeSpace'];
                        } elseif ($parameter->isDefaultValueAvailable()) {
                            $args[$parameterName] = $parameter->getDefaultValue();
                        } else {
                            throw new \RuntimeException("Cannot resolve Knowledge\Space for parameter '{$parameterName}' in '{$commandClass}'.");
                        }
                        break;
                    case 'string': // For knowledgeBasePath
                        if ($parameterName === 'knowledgeBasePath') {
                             if (isset($commandConfig['knowledgeBasePath'])) {
                                $args[$parameterName] = $commandConfig['knowledgeBasePath'];
                            } elseif ($parameter->isDefaultValueAvailable()) {
                                $args[$parameterName] = $parameter->getDefaultValue();
                            } else {
                                throw new \RuntimeException("Cannot resolve string 'knowledgeBasePath' for parameter '{$parameterName}' in '{$commandClass}'.");
                            }
                        }
                        break;
                    default:
                        // If type is not recognized, try to get default value
                        if ($parameter->isDefaultValueAvailable()) {
                            $args[$parameterName] = $parameter->getDefaultValue();
                        } else {
                            throw new \RuntimeException("Cannot resolve unknown type '{$typeName}' for parameter '{$parameterName}' in '{$commandClass}'.");
                        }
                        break;
                }
            } elseif ($parameter->isDefaultValueAvailable()) {
                $args[$parameterName] = $parameter->getDefaultValue();
            } else {
                // Untyped parameters without default values cannot be resolved automatically
                throw new \RuntimeException("Cannot resolve untyped parameter '{$parameterName}' in '{$commandClass}'.");
            }
        }

        return $args;
    }
}
