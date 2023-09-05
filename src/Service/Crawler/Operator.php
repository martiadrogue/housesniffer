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

class Operator
{
    private Retriever $retriever;
    private Dumper $dumper;
    private LoggerInterface $logger;
    private Parser $parser;

    private HintParser $hintRequestProvider;
    private HintParser $hintContentProvider;

    private int $currentPage;

    public function __construct(Retriever $retriever, Dumper $dumper, string $target, LoggerInterface $logger)
    {
        $this->retriever = $retriever;
        $this->dumper = $dumper;
        $this->logger = $logger;

        $this->currentPage = 1;

        $this->hintRequestProvider = HintService::parseHintsRequest($target, $logger);
        $this->hintContentProvider = HintService::parseHintsContent($target, $logger);
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
        $this->hintRequestProvider->setPage($this->currentPage);
        $stream = $this->retriever->fetch($this->hintRequestProvider);

        $this->initParser($stream);
        $this->dumper->persist($this->parser->parse());

        $this->currentPage += 1;
        $this->parser->seekPage($this->currentPage);
    }

    private function initParser(string $stream): void
    {
        if (empty($this->parser)) {
            $this->parser = new Parser(
                $this,
                $this->buildInterpreterFromContentType($stream),
                $this->hintContentProvider->parse()
            );
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
