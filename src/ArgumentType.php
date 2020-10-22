<?php
/**
 *    ______            __             __
 *   / ____/___  ____  / /__________  / /
 *  / /   / __ \/ __ \/ __/ ___/ __ \/ /
 * / /___/ /_/ / / / / /_/ /  / /_/ / /
 * \______________/_/\__/_/   \____/_/
 *    /   |  / / /_
 *   / /| | / / __/
 *  / ___ |/ / /_
 * /_/ _|||_/\__/ __     __
 *    / __ \___  / /__  / /____
 *   / / / / _ \/ / _ \/ __/ _ \
 *  / /_/ /  __/ /  __/ /_/  __/
 * /_____/\___/_/\___/\__/\___/
 *
 */

namespace MichielGerritsen\GraphqlToDto;

use MichielGerritsen\GraphqlToDto\DTO\ArgumentType as ArgumentTypeDTO;

class ArgumentType
{
    /**
     * @var Configuration
     */
    private $configuration;

    public function __construct(
        Configuration $configuration
    ) {
        $this->configuration = $configuration;
    }

    public function calculate(string $variableName, array $definition): ArgumentTypeDTO
    {
        $kind = $definition['type']['kind'];
        if ($kind == 'NON_NULL') {
            $kind = $definition['type']['ofType']['kind'];
        }

        if ($kind == 'LIST' && $definition['type']['ofType']['kind'] == 'SCALAR') {
            return $this->createScalarList($definition);
        }

        if ($kind == 'LIST') {
            return $this->createList($variableName, $definition);
        }

        if ($kind == 'OBJECT') {
            return $this->createObject($definition);
        }

        if ($kind == 'SCALAR' || $kind == 'NON_NULL') {
            return $this->createScalar($definition);
        }

        if ($kind == 'ENUM') {
            return $this->createEnum($definition);
        }

        if ($kind == 'INTERFACE') {
            return $this->createInterface($definition);
        }

        throw new \Exception(sprintf('There is not implementation for "%s" kind', $kind));
    }

    private function createList(string $variableName, array $definition): ArgumentTypeDTO
    {
        $class = $definition['type']['ofType']['name'];
        if (!$class) {
            $class = $definition['type']['ofType']['ofType']['name'];
        }

        return new ArgumentTypeDTO(
            'array',
            $class . '[]',
            'Assertion::allIsInstanceOf($' . $variableName . ', ' . $class . '::class);'
        );
    }

    private function createObject(array $definition)
    {
        $name = $definition['type']['name'];
        if (!$name) {
            $name = $definition['type']['ofType']['name'];
        }

        $class = '\\' . $this->configuration->getNamespace() . ucfirst($name);

        return new ArgumentTypeDTO(
            $class,
            $class,
            null
        );
    }

    private function createScalar(array $definition)
    {
        $type = strtolower($definition['type']['name']);

        if (!$type) {
            $type = 'string';
        }

        if ($type == 'boolean') {
            $type = 'bool';
        }

        return new ArgumentTypeDTO(
            $type,
            $type,
            null
        );
    }

    private function createEnum(array $definition)
    {
        // TODO
        return new ArgumentTypeDTO(
            'string',
            'string',
            null
        );
    }

    private function createInterface(array $definition)
    {
        $class = $this->configuration->getNamespace() . ucfirst($definition['name']) . 'Interface';
        return new ArgumentTypeDTO(
            $class,
            $class,
            null
        );
    }

    private function createScalarList(array $definition)
    {
        return new ArgumentTypeDTO(
            'array',
            'string[]',
            null
        );
    }
}
