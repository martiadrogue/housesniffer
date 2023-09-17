<?php

namespace App\Service\Crawler;

use Psr\Log\LoggerInterface;
use App\Service\Crawler\Dumper;
use App\Service\Crawler\Parser;
use App\Service\Crawler\Retriever;
use App\Service\Pointer\HintParser;
use App\Service\Pointer\HintService;
use App\Service\Crawler\Style\Interpreter;
use App\Service\Crawler\Style\JsonInterpreter;
use App\Service\Crawler\Style\MarkupInterpreter;
use Symfony\Contracts\Translation\TranslatableInterface;

class Operator
{
    private Retriever $retriever;
    private Dumper $dumper;
    private LoggerInterface $logger;
    private Parser $parser;
    private Interpreter $interpreter;

    private HintParser $hintRequestProvider;

    private int $currentPage;

    public function __construct(Retriever $retriever, Dumper $dumper, string $target, LoggerInterface $logger)
    {
        $this->retriever = $retriever;
        $this->dumper = $dumper;
        $this->logger = $logger;

        $this->currentPage = 1;

        $this->hintRequestProvider = HintService::parseHintsRequest($target, $logger);
        $this->parser = new Parser(
            $this,
            HintService::parseHintsContent($target, $logger)->parse()
        );
    }

    /**
     * Starts fetching content
     *
     * @param string[] $headerList
     * @param integer $delay
     * @return void
     */
    public function run(array $headerList, int $delay): void
    {
        $this->hintRequestProvider->setHeaderList($headerList);
        $this->hintRequestProvider->setDelay($delay);

        $this->update();
    }

    public function update(): void
    {
        $stream = $this->getStream();
        $data = $this->getData($stream);
        $this->dumper->persist($data);

        $this->currentPage += 1;
        $this->parser->seekPage($this->currentPage);
    }

    private function getData(string $stream): array
    {
        $this->setInterpreter($stream);

        return $this->parser->parse($stream);
    }

    private function getStream(): string
    {
        $this->hintRequestProvider->setLocation($this->currentPage);

        return $this->retriever->fetch($this->hintRequestProvider);
    }

    private function setInterpreter(string $stream): void
    {
        if (empty($this->interpreter)) {
            $this->interpreter = $this->buildInterpreterFromContentType($stream);
            $this->parser->setStyle($this->interpreter);
        }
    }

    private function buildInterpreterFromContentType(string $stream): Interpreter
    {
        $firstChar = substr(ltrim($stream), 0, 1);

        if ('<' == $firstChar) {
            return new MarkupInterpreter($stream, $this->logger);
        }

        if (in_array($firstChar, ['{', '[', ])) {
            return new JsonInterpreter($stream, $this->logger);
        }

        return new MarkupInterpreter($stream, $this->logger);
    }
}
