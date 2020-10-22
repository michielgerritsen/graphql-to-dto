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

    public function __construct(
        Introspection $introspection,
        DtoGenerator $dtoGenerator,
        Configuration $configuration
    ) {
        parent::__construct();

        $this->introspection = $introspection;
        $this->dtoGenerator = $dtoGenerator;
        $this->configuration = $configuration;
    }

    protected function configure()
    {
        $this->setName('generate');
        $this->setDescription('Run the DTO generator');
        $this->addArgument('endpoint', InputArgument::REQUIRED, 'The endpoint that contains as a source for the DTO generator');
        $this->addArgument('namespace', InputArgument::REQUIRED, 'What (PHP) namespace should be used?');
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

        $output->writeln('<info>Reading ' . $this->configuration->getEndpoint() . '</info>');
        $result = $this->introspection->run();

        $this->dtoGenerator->setOutput($output);
        foreach ($result['types'] as $type) {
            $this->dtoGenerator->generate($type);
        }
//        var_dump($result);
        exit;
    }
}
