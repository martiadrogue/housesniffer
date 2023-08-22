<?php

namespace App\Service\Crawler;

use Psr\Log\LoggerInterface;
use App\Service\Crawler\Parser;
use Symfony\Component\Uid\Uuid;
use App\Service\Crawler\Retriever;
use App\Service\Crawler\Style\Interpreter;
use App\Service\Crawler\Style\JsonInterpreter;
use App\Service\Crawler\Style\MarkupInterpreter;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class Operator
{
    private string $name;
    private Retriever $retriever;
    private LoggerInterface $logger;
    private int $currentPageNumbe;
    private Uuid $id;

    private const CSV_TMP_PATH = 'var/tmp/csv/';
    private const CSV_PATH = 'var/csv/';

    public function __construct(Retriever $retriever, LoggerInterface $logger)
    {
        $this->retriever = $retriever;
        $this->logger = $logger;
        $this->id = Uuid::v4();

        $this->currentPageNumbe = 1;
    }

    public function update(): void
    {
        $stream = $this->retriever->fetchList($this->name, $this->currentPageNumbe);

        $fileType = $this->solveContentType($stream);
        $interpreter = $this->buildInterpreter($fileType);

        $parser = new Parser($this);
        $parser->setInterpreter($interpreter);
        $data = $parser->parse($stream);

        $this->persistData($data);

        $this->currentPageNumbe += 1;
        $parser->seekPage($stream, $this->currentPageNumbe);
    }

    public function setTarget(string $name): void
    {
        $this->name = $name;
    }

    public function getTarget(): string
    {
        return $this->name;
    }

    public function secureResults(): void
    {
        $filesystem = new Filesystem();
        $fileName = sprintf('%s_%s.csv', $this->name, $this->id);

        $filesystem->rename(self::CSV_TMP_PATH . $fileName, self::CSV_PATH . $fileName);
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
    private function persistData(array $data): void
    {
        $context = [];
        $serializer = new Serializer([new ObjectNormalizer()], [new CsvEncoder()]);
        $filesystem = new Filesystem();
        $fileName = self::CSV_TMP_PATH;
        $fileName .= sprintf('%s_%s.csv', $this->name, $this->id);

        if ($filesystem->exists($fileName)) {
            $context['no_headers'] = true;/*  */
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
