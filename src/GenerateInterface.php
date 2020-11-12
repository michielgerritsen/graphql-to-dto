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

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpNamespace;

class GenerateInterface
{
    /**
     * @var ClassType
     */
    private $interface;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var Converter
     */
    private $converter;

    /**
     * @var ArgumentType
     */
    private $argumentType;

    public function __construct(
        Configuration $configuration,
        Converter $converter,
        ArgumentType $argumentType
    ) {
        $this->configuration = $configuration;
        $this->converter = $converter;
        $this->argumentType = $argumentType;
    }

    public function generate(PhpNamespace $namespace, array $definition): ClassType
    {
        $this->interface = $namespace->addInterface($definition['name']);

        foreach ($definition['fields'] as $field) {
            $this->addField($field);
        }

        return $this->interface;
    }

    private function addField($field)
    {
        if ($field['isDeprecated'] && !$this->configuration->useDeprecated()) {
            return;
        }

        $variableName = $this->converter->camelCase($field['name'], false);
        $type = $this->argumentType->calculate($variableName, $field);

        $method = $this->interface->addMethod('get' . $this->converter->camelCase($field['name']));
        $method->setReturnType($type->getConstructorTypeHint());
    }
}
