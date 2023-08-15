<?php

namespace App\Command;

use App\Service\PerformanceService;
use App\Service\Crawler\Operator;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\BrowserKit\HttpBrowser;
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
    private PerformanceService $performanceTracker;
    private Operator $crawler;

    public function __construct(PerformanceService $performanceTracker = null, Operator $crawler = null)
    {
        $this->performanceTracker = $performanceTracker;
        $this->crawler = $crawler;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('target', InputArgument::REQUIRED, 'Target to hunt')
            ->addOption(
                'mode',
                null,
                InputOption::VALUE_OPTIONAL,
                'Way to hunt the data, `details` or `list`, `list` by default',
                'list'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // !Head zone
        $this->performanceTracker->start();
        $io = new SymfonyStyle($input, $output);

        // !Input zones
        $target = $input->getArgument('target');
        if ($target) {
            $io->note(sprintf('Your target is: %s', $target));
        }

        $mode = $input->getOption('mode');
        if ($input->getOption('mode')) {
            $io->note(sprintf('Your attack vector is from: %s', $mode));
        }

        // !TODO zone
        $name = sprintf('%s_%s', $target, $mode);
        $this->crawler->setTarget($name);
        $this->crawler->update();

        // !Food zone
        $output = $this->performanceTracker->stop();
        $io->text($output);

        return Command::SUCCESS;
    }
}
