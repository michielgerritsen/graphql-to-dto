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

class DefinitionLibrary
{
    /**
     * @var array|null
     */
    private $definition = null;

    public function setDefinition(array $list)
    {
        if ($this->definition !== null) {
            throw new \Exception('The definition is already set');
        }

        foreach ($list as $definition) {
            $this->definition[$definition['name']] = $definition;
        }
    }

    public function get($definition): ?array
    {
        return $this->definition[$definition] ?? null;
    }
}
