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

use Nette\PhpGenerator\Method;

class FromArray
{
    /**
     * @var array
     */
    private $arrayTypes = [];

    /**
     * @var array
     */
    private $arguments = [];

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var Converter
     */
    private $converter;

    public function __construct(
        Configuration $configuration,
        Converter $converter
    ) {
        $this->configuration = $configuration;
        $this->converter = $converter;
    }

    public function generate($fields, Method $method)
    {
        $this->arrayTypes = [];
        $this->arguments = [];

        $method->setStatic(true);
        $method->setReturnType('self');
        $method->addParameter('data')->setType('array');

        foreach ($fields as $field) {
            $this->addField($field);
        }

        $method->setBody(
            implode(PHP_EOL, $this->arrayTypes) . PHP_EOL .
            'return new static(' . PHP_EOL .
            "\t" . implode(',' . PHP_EOL . "\t", $this->arguments) . PHP_EOL .
            ');'
        );
    }

    private function addField($field)
    {
        if ($field['isDeprecated'] && !$this->configuration->useDeprecated()) {
            return;
        }

        $kind = $field['type']['kind'];
        if ($kind == 'NON_NULL') {
            $kind = $field['type']['ofType']['kind'];
        }

        if ($kind == 'LIST' && ($field['type']['ofType']['kind'] == 'OBJECT' || $field['type']['ofType']['kind'] == 'INTERFACE')) {
            $this->createObjectList($field);
            return;
        }

        if ($kind == 'LIST' && $field['type']['kind'] == 'NON_NULL') {
            $this->createObjectList($field);
            return;
        }

        if ($kind == 'OBJECT' || $kind == 'INTERFACE') {
            $this->createObject($field);
            return;
        }

        // TODO: Make enum an actual enum.
        if ($kind == 'SCALAR' || $kind == 'ENUM') {
            $this->createScalar($field);
            return;
        }

        if ($kind == 'LIST' && $field['type']['ofType']['kind'] == 'SCALAR') {
            $this->createScalarList($field);
            return;
        }

        throw new \Exception(sprintf('We did not yet impelement kind "%s"', $kind));
    }

    private function createObjectList($field)
    {
        $name = $this->converter->camelCase($field['name'], false);
        $class = $field['type']['ofType']['name'];

        if (!$class) {
            $class = $field['type']['ofType']['ofType']['name'];
        }

        $this->arrayTypes[] = '$' . $name . ' = [];
foreach ($data[\'' . $field['name'] . '\'] as $subData) {
    $' . $name . '[] = ' . $class . '::fromArray($subData);
}
        ';

        $this->arguments[] = '$' . $name;
    }

    private function createObject($field)
    {
        $name = $field['type']['name'];
        if ($field['type']['kind'] == 'NON_NULL') {
            $name = $field['type']['ofType']['name'];
        }

        $this->arguments[] = $name . '::fromArray($data[\'' . $field['name'] . '\'])';
    }

    private function createScalar($field)
    {
        $this->arguments[] = '$data[\'' . $field['name'] . '\']';
    }

    private function createScalarList($field)
    {
        $this->arguments[] = '$data[\'' . $field['name'] . '\']';
    }
}
