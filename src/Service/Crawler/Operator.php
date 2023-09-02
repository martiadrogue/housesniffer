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

    public function update(): void
    {
        $this->hintRequestProvider->setPage($this->currentPage);
        $stream = $this->retriever->fetch($this->hintRequestProvider);

        $this->parser = $this->getParser($stream);
        $this->dumper->persist($this->parser->parse($stream));

        $this->currentPage += 1;
        $this->parser->seekPage($stream, $this->currentPage);
    }

    private function getParser(string $stream): Parser
    {
        if (empty($this->parser)) {
            $fileType = $this->solveContentType($stream);
            return new Parser(
                $this,
                $this->buildInterpreter($fileType),
                $this->hintContentProvider->parse()
            );
        }

        return $this->parser;
    }

    private function solveContentType(string $stream): string
    {
        $firstChar = substr(ltrim($stream), 0, 1);

        if ('<' == $firstChar) {
            return 'html';
        }

        if (in_array($firstChar, ['{', '[', ])) {
            return 'json';
        }

        return '';
    }

    private function buildInterpreter(string $fileType): Interpreter
    {
        if ('html' == $fileType) {
            return new MarkupInterpreter($this->logger);
        }

        if ('json' == $fileType) {
            return new JsonInterpreter($this->logger);
        }

        return new MarkupInterpreter($this->logger);
    }
}
