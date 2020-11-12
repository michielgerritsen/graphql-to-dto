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

use Illuminate\Container\Container as iocContainer;

class Container
{
    /**
     * @var iocContainer
     */
    private $container;

    public function __construct()
    {
        $this->container = new iocContainer();
        $this->container->singleton(Configuration::class);
        $this->container->singleton(DefinitionLibrary::class);
    }

    public function make(string $class)
    {
        return $this->container->make($class);
    }

    public function instance($abstract, $concrete = null, $shared = false)
    {
        $this->container->instance($abstract, $concrete, $shared);
    }
}
