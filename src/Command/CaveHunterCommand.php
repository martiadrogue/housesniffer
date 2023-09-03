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
        $this->addArgumentsAndOptions();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->performanceTracker->start();
        $io = new SymfonyStyle($input, $output);
        $target = $this->getTarget($input, $io);
        $mode = $this->getMode($input, $io);
        $headerList = $this->getHeaderList($input, $io);
        $delay = $this->getDelay($input, $io);

        $this->process($target, $mode, $headerList, $delay);
        $this->printPerformance($io);

        return Command::SUCCESS;
    }

    private function printPerformance(SymfonyStyle $io): void
    {
        $output = $this->performanceTracker->stop();
        $io->text($output);
    }

    private function process(string $target, string $mode, array $headerList, int $delay): void
    {
        $target = sprintf('%s_%s', $target, $mode);
        $dumper = new Dumper($target, $this->logger);
        $operator = new Operator($this->retriever, $dumper, $target, $this->logger);

        $operator->run($headerList, $delay);
        $dumper->secure();
    }

    private function getTarget(InputInterface $input, SymfonyStyle $io): string
    {
        $target = $input->getArgument('target');
        $io->note(sprintf('Your target is: %s', $target));

        return $target;
    }

    private function getMode(InputInterface $input, SymfonyStyle $io): string
    {
        $mode = $input->getOption('mode');
        if ($mode) {
            $io->note(sprintf('Your attack vector is from: %s', $mode));
        }

        return $mode;
    }

    private function getDelay(InputInterface $input, SymfonyStyle $io): int
    {
        $delay = $input->getOption('delay');
        if ($delay) {
            $io->note(sprintf('Time to wait between Requests: %ss', $delay));
        }

        return $delay;
    }

    private function getHeaderList(InputInterface $input, SymfonyStyle $io): array
    {
        $incipit = $input->getOption('incipit');
        if ($incipit) {
            $io->note(sprintf('Hunting with custom headers'));
        }

        return $incipit;
    }

    private function addArgumentsAndOptions(): void
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
                'incipit',
                'i',
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Overwrite or add new headers',
            )
            ->addOption(
                'proxy',
                'p',
                InputOption::VALUE_NONE,
                'Proxy to hide your identity'
            )

        ;
    }
}
