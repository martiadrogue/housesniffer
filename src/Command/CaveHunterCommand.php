<?php

namespace App\Command;

use Symfony\Component\Yaml\Yaml;
use App\Service\Crawler\Operator;
use App\Service\PerformanceService;
use App\Service\Pointer\HintConfiguration;
use App\Service\Pointer\HintValidator;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Exception\ParseException;

#[AsCommand(
    name: 'app:cave-hunter',
    description: 'Scan the world tirelessly for habitable caves',
)]
class CaveHunterCommand extends Command
{
    private PerformanceService $performanceTracker;
    private Operator $crawler;

    public function __construct(PerformanceService $performanceTracker, Operator $crawler)
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

        // !Input zones
        $target = $input->getArgument('target');
        $io->note(sprintf('Your target is: %s', $target));

        $mode = $input->getOption('mode');
        if ($input->getOption('mode')) {
            $io->note(sprintf('Your attack vector is from: %s', $mode));
        }

        // !Validate zone
        // do things ...

        // !TODO zone
        $target = sprintf('%s_%s', $target, $mode);
        $this->crawler->loadHints($target);
        $this->crawler->update();
        $this->crawler->secureResults();

        // !Food zone
        $output = $this->performanceTracker->stop();
        $io->text($output);

        return Command::SUCCESS;
    }
}
