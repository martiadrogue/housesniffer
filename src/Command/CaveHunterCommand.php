<?php

namespace App\Command;

use App\Service\Crawler\Dumper;
use Psr\Log\LoggerInterface;
use App\Service\Crawler\Operator;
use App\Service\Crawler\Retriever;
use App\Service\PerformanceService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:cave-hunter',
    description: 'Scan the world tirelessly for habitable caves',
)]
class CaveHunterCommand extends Command
{
    private Retriever $retriever;
    private PerformanceService $performanceTracker;
    private LoggerInterface $logger;

    public function __construct(Retriever $retriever, PerformanceService $performanceTracker, LoggerInterface $logger)
    {
        $this->performanceTracker = $performanceTracker;
        $this->retriever = $retriever;
        $this->logger = $logger;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('target', InputArgument::REQUIRED, 'Target to hunt')
            ->addOption(
                'mode',
                'm',
                InputOption::VALUE_OPTIONAL,
                'Way to hunt the data, `details` or `list`, `list` by default',
                'list'
            )
            ->addOption(
                'delay',
                'd',
                InputOption::VALUE_OPTIONAL,
                'Delay between requests in seconds',
                0
            )
            ->addOption(
                'headers',
                'hd',
                InputOption::VALUE_OPTIONAL,
                'Overwrite headers',
                0
            )
            ->addOption(
                'proxy',
                'p',
                InputOption::VALUE_NONE,
                'Proxy to hide your identy'
            )

        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // !Head zone
        $this->performanceTracker->start();
        $io = new SymfonyStyle($input, $output);

        // !Input zone
        $target = $input->getArgument('target');
        $io->note(sprintf('Your target is: %s', $target));

        $mode = $input->getOption('mode');
        if ($input->getOption('mode')) {
            $io->note(sprintf('Your attack vector is from: %s', $mode));
        }

        // !TODO zone
        $this->parseTarget($target, $mode);

        // !Food zone
        $this->printPerformance($io);

        return Command::SUCCESS;
    }

    private function printPerformance(SymfonyStyle $io): void
    {
        $output = $this->performanceTracker->stop();
        $io->text($output);
    }

    private function parseTarget(string $target, string $mode): void
    {
        $target = sprintf('%s_%s', $target, $mode);
        $dumper = new Dumper($target, $this->logger);
        $crawler = new Operator($this->retriever, $dumper, $target, $this->logger);
        $crawler->update();
        $dumper->secure();
    }
}
