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

    /**
     * @var ClassImplements
     */
    private $classImplements;

    /**
     * @var GenerateInterface
     */
    private $generateInterface;

    /**
     * @var GenerateClass
     */
    private $generateClass;

    public function __construct(
        ArgumentType $argumentType,
        FromArray $fromArray,
        Configuration $configuration,
        Converter $converter,
        ClassImplements $classImplements,
        GenerateInterface $generateInterface,
        GenerateClass $generateClass
    ) {
        $this->argumentType = $argumentType;
        $this->configuration = $configuration;
        $this->fromArray = $fromArray;
        $this->converter = $converter;
        $this->classImplements = $classImplements;
        $this->generateInterface = $generateInterface;
        $this->generateClass = $generateClass;
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

        $this->getObject($namespace, $definition);

        $printer = new PsrPrinter;

        @mkdir(__DIR__ . '/../output');
        file_put_contents(
            __DIR__ . '/../output/' . $definition['name'] . '.php',
            '<?php' . PHP_EOL . $printer->printNamespace($namespace)
        );
    }

    private function getObject(PhpNamespace $namespace, array $definition): ClassType
    {
        if ($definition['kind'] == 'INTERFACE') {
            return $this->generateInterface->generate($namespace, $definition);
        }

        return $this->generateClass->generate($namespace, $definition);
    }
}
