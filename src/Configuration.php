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

class Configuration
{
    /**
     * @var string
     */
    private $endpoint = '';

    /**
     * @var string
     */
    private $namespace = '';

    /**
     * @var bool
     */
    private $useDeprecated = false;

    /**
     * @var string
     */
    private $folder = '';

    /**
     * @var array|null
     */
    private $blockList = null;

    /**
     * @return string
     */
    public function getEndpoint(): string
    {
        return $this->endpoint;
    }

    /**
     * @param string $endpoint
     */
    public function setEndpoint(string $endpoint): void
    {
        $this->endpoint = $endpoint;
    }

    /**
     * @return string
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * @param string $namespace
     */
    public function setNamespace(string $namespace): void
    {
        $namespace = str_replace(['//', '/'], '\\', $namespace);

        $this->namespace = trim($namespace, '\\') . '\\';
    }

    /**
     * @return bool
     */
    public function useDeprecated(): bool
    {
        return $this->useDeprecated;
    }

    /**
     * @param bool $useDeprecated
     */
    public function setUseDeprecated(bool $useDeprecated): void
    {
        $this->useDeprecated = $useDeprecated;
    }

    /**
     * @return string
     */
    public function getFolder(): string
    {
        return $this->folder;
    }

    /**
     * @param string $folder
     */
    public function setFolder(string $folder): void
    {
        $this->folder = $folder;
    }

    public function getBlockList(): ?array
    {
        return $this->blockList;
    }

    public function setBlockList(?string $blockList): void
    {
        if (!$blockList) {
            return;
        }

        $this->blockList = $this->parseList($blockList);
    }

    private function parseList($filename)
    {
        if (!file_exists($filename) || !is_readable($filename)) {
            throw new \Exception(sprintf('File "%s" does not exists or is not readable', $filename));
        }

        $contents = file_get_contents($filename);
        $result = json_decode($contents, true);

        if (json_last_error() != JSON_ERROR_NONE) {
            throw new \Exception(sprintf('The file "%s" does not contain valid JSON', $filename));
        }

        return $result;
    }
}
