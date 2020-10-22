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

namespace MichielGerritsen\GraphqlToDto\DTO;

class ArgumentType
{
    /**
     * @var string
     */
    private $constructorTypeHint;

    /**
     * @var string
     */
    private $getterReturnType;

    /**
     * @var string|null
     */
    private $constructorValidation;

    public function __construct(
        string $constructorTypeHint,
        string $getterReturnType,
        string $constructorValidation = null
    ) {
        $this->constructorTypeHint = $constructorTypeHint;
        $this->getterReturnType = $getterReturnType;
        $this->constructorValidation = $constructorValidation;
    }

    /**
     * @return string
     */
    public function getConstructorTypeHint(): string
    {
        return $this->constructorTypeHint;
    }

    /**
     * @return string
     */
    public function getGetterReturnType(): string
    {
        return $this->getterReturnType;
    }

    /**
     * @return string
     */
    public function getConstructorValidation(): ?string
    {
        return $this->constructorValidation;
    }
}
