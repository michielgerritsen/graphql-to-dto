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

class DtoList
{
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

    public function build(array $data): array
    {
        if ($this->configuration->getBlockList()) {
            $data = array_filter($data, [$this, 'removeBlockedEntities']);
            $data = array_map([$this, 'removeBlockedFields'], $data);
        }

        return $data;
    }

    private function removeBlockedEntities($entity): bool
    {
        $name = $this->converter->camelCase($entity['name']);
        $blockList = $this->configuration->getBlockList();
        if (!isset($blockList[$name])) {
            return true;
        }

        if ($blockList[$name][0] == '*') {
            return false;
        }

        return true;
    }

    private function removeBlockedFields($entity): array
    {
        $name = $this->converter->camelCase($entity['name']);
        $blockList = $this->configuration->getBlockList();
        if (!isset($blockList[$name])) {
            return $entity;
        }

        $blockedFields = $blockList[$name];

        $fields = array_filter($entity['fields'], function ($field) use ($blockedFields) {
            return !in_array($field['name'], $blockedFields);
        });

        $entity['fields'] = array_values($fields);

        return $entity;
    }
}
