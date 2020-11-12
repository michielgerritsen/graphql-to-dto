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

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Generator extends Command
{
    /**
     * @var Introspection
     */
    private $introspection;

    /**
     * @var DtoGenerator
     */
    private $dtoGenerator;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var DtoList
     */
    private $dtoList;

    /**
     * @var DefinitionLibrary
     */
    private $definitionLibrary;

    public function __construct(
        Introspection $introspection,
        DtoGenerator $dtoGenerator,
        Configuration $configuration,
        DtoList $dtoList,
        DefinitionLibrary $definitionLibrary
    ) {
        parent::__construct();

        $this->introspection = $introspection;
        $this->dtoGenerator = $dtoGenerator;
        $this->configuration = $configuration;
        $this->dtoList = $dtoList;
        $this->definitionLibrary = $definitionLibrary;
    }

    protected function configure()
    {
        $this->setName('generate');
        $this->setDescription('Run the DTO generator');
        $this->addArgument('endpoint', InputArgument::REQUIRED, 'The endpoint that contains as a source for the DTO generator');
        $this->addArgument('namespace', InputArgument::REQUIRED, 'What (PHP) namespace should be used?');
        $this->addOption('blocklist', 'b', InputArgument::OPTIONAL, 'Provide a blocklist');
        // TODO
//        $this->addArgument('folder', InputArgument::REQUIRED, 'To what folder should we output the generated DTO\'s?');
        $this->addOption('include-deprecated', 'd', InputOption::VALUE_OPTIONAL, 'Include deprecated arguments?', false);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->configuration->setEndpoint($input->getArgument('endpoint'));
        $this->configuration->setNamespace($input->getArgument('namespace'));
//        $this->configuration->setFolder($input->getArgument('folder'));
        $this->configuration->setUseDeprecated($input->getOption('include-deprecated'));
        $this->configuration->setBlockList($input->getOption('blocklist'));

        $output->writeln('<info>Reading ' . $this->configuration->getEndpoint() . '</info>');
        $result = $this->introspection->run();

        $this->dtoGenerator->setOutput($output);
        $list = $this->dtoList->build($result['types']);
        $this->definitionLibrary->setDefinition($list);
        foreach ($list as $type) {
            $this->dtoGenerator->generate($type);
        }

        return 0;
    }
}
