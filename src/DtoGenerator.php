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
use Nette\PhpGenerator\PsrPrinter;
use Symfony\Component\Console\Output\OutputInterface;

class DtoGenerator
{
    const IGNORED = [
        'Query',
        'String',
        '__Directive',
        '__EnumValue',
        '__Field',
        '__InputValue',
        '__Schema',
        '__Type',
    ];

    /**
     * @var ArgumentTypeDTO[]
     */
    private $constructorArguments = [];

    /**
     * @var ArgumentType
     */
    private $argumentType;

    /**
     * @var FromArray
     */
    private $fromArray;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var Converter
     */
    private $converter;

    public function __construct(
        ArgumentType $argumentType,
        FromArray $fromArray,
        Configuration $configuration,
        Converter $converter
    ) {
        $this->argumentType = $argumentType;
        $this->configuration = $configuration;
        $this->fromArray = $fromArray;
        $this->converter = $converter;
    }

    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function generate(array $definition)
    {
        $this->constructorArguments = [];
        if (in_array($definition['name'], static::IGNORED) || !$definition['fields']) {
            return;
        }

        $this->output->writeln(sprintf('Generating DTO for type "%s"', $definition['name']));
        $namespace = new PhpNamespace(trim($this->configuration->getNamespace(), '\\'));

        $class = $namespace->addClass($definition['name']);
        $constructor = $class->addMethod('__construct');
        $this->fromArray->generate($definition['fields'], $class->addMethod('fromArray'));

        foreach ($definition['fields'] as $field) {
            $this->addField($class, $field);
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

        $printer = new PsrPrinter;

        @mkdir(__DIR__ . '/../output');
        file_put_contents(
            __DIR__ . '/../output/' . $definition['name'] . '.php',
            '<?php' . PHP_EOL . $printer->printNamespace($namespace)
        );
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
