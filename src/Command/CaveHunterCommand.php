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
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // !Head zone
        $this->performanceTracker->start();

        // !Options zones
        $io = new SymfonyStyle($input, $output);
        $arg1 = $input->getArgument('arg1');

        if ($arg1) {
            $io->note(sprintf('You passed an argument: %s', $arg1));
        }

        if ($input->getOption('option1')) {
            // ...
        }

        // !TODO zone
        $this->crawler->update();

        // !Food zone
        $output = $this->performanceTracker->stop();
        $io->text($output);

        return Command::SUCCESS;
    }
}
