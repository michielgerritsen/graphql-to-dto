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

namespace MichielGerritsen\GraphqlToDto\Test;

use MichielGerritsen\GraphqlToDto\Configuration;
use MichielGerritsen\GraphqlToDto\Container;
use MichielGerritsen\GraphqlToDto\DtoList;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

class DtoListTest extends TestCase
{
    /**
     * @var Container
     */
    private $container;

    protected function setUp(): void
    {
        $this->container = new Container();
    }

    public function testIncludesEverythingWhenNoBlockOrAllowlistIsSet()
    {
        /** @var DtoList $instance */
        $instance = $this->container->make(DtoList::class);

        $result = $instance->build([
            [
                'name' => 'test_entity',
                'fields' => [
                    ['name' => 'ignoredField'],
                    ['name' => 'allowedField'],
                ]
            ]
        ]);

        $this->assertCount(1, $result);
        $this->assertCount(2, $result[0]['fields']);
    }

    public function testIgnoresItemsFromBlocklist()
    {
        $root = vfsStream::setup('root');
        $file = vfsStream::newFile('blocklist.json')->at($root)->withContent(json_encode([
            'testEntity' => [
                'ignoredField',
            ]
        ]));

        /** @var DtoList $instance */
        $instance = $this->container->make(DtoList::class);

        /** @var Configuration $configuration */
        $configuration = $this->container->make(Configuration::class);
        $configuration->setBlockList($file->url());

        $result = $instance->build([
            [
                'name' => 'test_entity',
                'fields' => [
                    ['name' => 'ignoredField'],
                    ['name' => 'allowedField'],
                ]
            ]
        ]);

        $this->assertEquals('test_entity', $result[0]['name']);
        $this->assertCount(1, $result[0]['fields']);
        $this->assertEquals('allowedField', $result[0]['fields'][0]['name']);
    }

    public function testRemovesEntityWhenUsingAWildcard()
    {
        $root = vfsStream::setup('root');
        $file = vfsStream::newFile('blocklist.json')->at($root)->withContent(json_encode([
            'testEntity' => [
                '*',
            ]
        ]));

        /** @var DtoList $instance */
        $instance = $this->container->make(DtoList::class);

        /** @var Configuration $configuration */
        $configuration = $this->container->make(Configuration::class);
        $configuration->setBlockList($file->url());

        $result = $instance->build([
            [
                'name' => 'test_entity',
                'fields' => [
                    ['name' => 'ignoredField'],
                    ['name' => 'allowedField'],
                ]
            ]
        ]);

        $this->assertCount(0, $result);
    }
}
