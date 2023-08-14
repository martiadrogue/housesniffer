<?php

namespace App\Service\Crawler;

use Psr\Log\LoggerInterface;
use App\Service\Crawler\Parser;
use Symfony\Component\Uid\Uuid;
use App\Service\Crawler\Retriever;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class Operator
{
    private Retriever $retriever;
    private LoggerInterface $logger;
    private int $currentPageNumbe;
    private string $fileName;

    private const CSV_PATH = 'var/csv/homes';

    public function __construct(Retriever $retriever, LoggerInterface $logger)
    {
        $this->retriever = $retriever;
        $this->logger = $logger;

        $this->fileName = self::CSV_PATH . '_' . Uuid::v4() . '.csv';
        $this->currentPageNumbe = 1;
    }

    public function update(): void
    {
        $stream = $this->retriever->fetchList($this->currentPageNumbe);
        $parser = new Parser($stream, $this, $this->logger);
        $data = $parser->parse();
        $this->persistData($data);

        $this->currentPageNumbe += 1;
        $parser->seekNextPage();
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

        if ($filesystem->exists($this->fileName)) {
            $context['no_headers'] = true;
        }

        $csv = $serializer->encode($data, 'csv', $context);
        $filesystem->appendToFile($this->fileName, $csv);
    }
}
