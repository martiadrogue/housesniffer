<?php

namespace App\Service\Crawler;

use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class Dumper
{
    private string $target;
    private int $id;

    private Filesystem $filesystem;
    private Serializer $serializer;
    private LoggerInterface $logger;

    private const CSV_TMP_PATH = 'var/tmp/csv/';
    private const CSV_PATH = 'var/csv/';

    public function __construct(string $target, LoggerInterface $logger)
    {
        $this->target = $target;
        $this->id = \time();

        $this->logger = $logger;
        $this->filesystem = new Filesystem();
        $this->serializer = new Serializer([new ObjectNormalizer()], [new CsvEncoder()]);
    }

    public function secure(): void
    {
        $fileName = $this->getFileName();

        $this->filesystem->rename(self::CSV_TMP_PATH . $fileName, self::CSV_PATH . $fileName);
        $this->logger->info('Content has been downloaded successfully');
    }

    /**
     * Persit data in a file
     *
     * @param string[] $data
     * @return void
     */
    public function persist(array $data): void
    {
        $fileName = self::CSV_TMP_PATH;
        $fileName .= $this->getFileName();

        $csv = $this->serializer->encode($data, 'csv', $this->getContext($fileName));
        $this->filesystem->appendToFile($fileName, $csv);
    }

    private function getContext(string $fileName): array
    {
        $context = [];
        if ($this->filesystem->exists($fileName)) {
            $context['no_headers'] = true;
        }

        return $context;
    }

    private function getFileName(): string
    {
        return sprintf('%s_%s.csv', $this->target, $this->id);
    }
}
