<?php

namespace App\Service\Crawler;

use Psr\Log\LoggerInterface;
use Symfony\Component\Yaml\Yaml;
use App\Service\Crawler\Operator;
use App\Service\Pointer\HintService;
use Symfony\Component\DomCrawler\Crawler;
use App\Service\Crawler\Style\Interpreter;

class Parser
{
    private Operator $operator;
    private Interpreter $interpreter;

    /**
     * List path to the data to scrap
     *
     * @var array<mixed>
     */
    private array $pathMap;

    public function __construct(Operator $operator, LoggerInterface $logger)
    {
        $this->operator = $operator;

        $target = $this->operator->getTarget();
        $this->pathMap = HintService::parseHintsContent($target, $logger);
    }

    public function setInterpreter(Interpreter $interpreter): void
    {
        $this->interpreter = $interpreter;
    }

    /**
     * Parse the stream of html to an array
     *
     * @return string[]
     */
    public function parse(string $stream): array
    {
        return $this->interpreter->parse($stream, $this->pathMap);
    }

    public function seekPage(string $stream, int $currentPage): void
    {
        $pageList = $this->interpreter->getPageList($stream, $this->pathMap['page']);

        if (in_array($currentPage, $pageList)) {
            $this->operator->update();
        }
    }
}
