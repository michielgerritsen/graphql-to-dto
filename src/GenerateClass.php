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
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpNamespace;

class GenerateClass
{
    /**
     * @var ArgumentTypeDTO[]
     */
    private $constructorArguments = [];

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var ClassImplements
     */
    private $classImplements;

    /**
     * @var FromArray
     */
    private $fromArray;

    /**
     * @var ArgumentType
     */
    private $argumentType;

    /**
     * @var Converter
     */
    private $converter;

    public function __construct(
        Configuration $configuration,
        ClassImplements $classImplements,
        FromArray $fromArray,
        ArgumentType $argumentType,
        Converter $converter
    ) {
        $this->configuration = $configuration;
        $this->classImplements = $classImplements;
        $this->fromArray = $fromArray;
        $this->argumentType = $argumentType;
        $this->converter = $converter;
    }

    public function generate(PhpNamespace $namespace, array $definition): ClassType
    {
        $this->constructorArguments = [];

        $object = $namespace->addClass($definition['name']);
        $this->classImplements->calculate($object, $definition);

        $constructor = $object->addMethod('__construct');
        $this->fromArray->generate($definition['fields'], $object->addMethod('fromArray'));

        foreach ($definition['fields'] as $field) {
            $this->addField($object, $field);
        }

        $body = [];
        $useValidation = false;
        foreach ($this->constructorArguments as $name => $type) {
            $name = $this->converter->camelCase($name, false);
            $constructor->addParameter($name)->setType($type->getConstructorTypeHint());
            $body[] = '$this->' . $name . ' = $' . $name . ';';

            if ($type->getConstructorValidation()) {
                $useValidation = true;
                array_unshift($body, $type->getConstructorValidation());
            }
        }

        if ($useValidation) {
            $namespace->addUse('Assert\Assertion');
        }

        $constructor->setBody(implode(PHP_EOL, $body));

        return $object;
    }

    private function addField(ClassType $class, $field)
    {
        if ($field['isDeprecated'] && !$this->configuration->useDeprecated()) {
            return;
        }

        $variableName = $this->converter->camelCase($field['name'], false);
        $type = $this->argumentType->calculate($variableName, $field);

        $class->addProperty($variableName)->setComment(PHP_EOL . '@var ' . $type->getGetterReturnType() . PHP_EOL);
        $method = $class->addMethod('get' . $this->converter->camelCase($field['name']));
        $method->setBody('return $this->' . $variableName . ';');
        $method->setComment('@return ' . $type->getGetterReturnType());
        $method->setReturnType($type->getConstructorTypeHint());

        $this->constructorArguments[$field['name']] = $type;
    }
}
