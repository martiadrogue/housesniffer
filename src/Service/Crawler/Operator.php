<?php

namespace App\Service\Crawler;

use Psr\Log\LoggerInterface;
use App\Service\Crawler\Parser;
use App\Service\Crawler\Retriever;
use App\Service\Pointer\HintService;
use App\Service\Pointer\HintParser;
use App\Service\Crawler\Style\Interpreter;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Serializer\Serializer;
use App\Service\Crawler\Style\JsonInterpreter;
use App\Service\Crawler\Style\MarkupInterpreter;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class Operator
{
    private string $target;
    private Retriever $retriever;
    private LoggerInterface $logger;
    private HintParser $hintRequestProvider;
    private HintParser $hintContentProvider;
    private int $currentPage;
    private int $id;

    private const CSV_TMP_PATH = 'var/tmp/csv/';
    private const CSV_PATH = 'var/csv/';

    public function __construct(Retriever $retriever, string $target, LoggerInterface $logger)
    {
        $this->retriever = $retriever;
        $this->logger = $logger;
        $this->target = $target;

        $this->id = \time();
        $this->currentPage = 1;

        $this->hintRequestProvider = HintService::parseHintsRequest($target, $logger);
        $this->hintContentProvider = HintService::parseHintsContent($target, $logger);
    }

    public function update(): void
    {
        $this->hintRequestProvider->setPage($this->currentPage);
        $stream = $this->retriever->fetchList($this->hintRequestProvider);

        $parser = $this->getParser($stream);
        $this->persistContentMap($parser->parse($stream));

        $this->currentPage += 1;
        $parser->seekPage($stream, $this->currentPage);
    }


    public function secureResults(): void
    {
        $filesystem = new Filesystem();
        $fileName = sprintf('%s_%s.csv', $this->target, $this->id);

        $filesystem->rename(self::CSV_TMP_PATH . $fileName, self::CSV_PATH . $fileName);
    }

    private function getParser(string $stream): Parser
    {
        $fileType = $this->solveContentType($stream);
        $parser = new Parser(
            $this,
            $this->buildInterpreter($fileType),
            $this->hintContentProvider->parse()
        );

        return $parser;
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

    /**
     * Persit data in a file
     *
     * @param string[] $data
     * @return void
     */
    private function persistContentMap(array $data): void
    {
        $context = [];
        $serializer = new Serializer([new ObjectNormalizer()], [new CsvEncoder()]);
        $filesystem = new Filesystem();
        $fileName = self::CSV_TMP_PATH;
        $fileName .= sprintf('%s_%s.csv', $this->target, $this->id);

        if ($filesystem->exists($fileName)) {
            $context['no_headers'] = true;
        }

        $csv = $serializer->encode($data, 'csv', $context);
        $filesystem->appendToFile($fileName, $csv);
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
