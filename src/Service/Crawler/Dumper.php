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

    private const CSV_TMP_PATH = 'var/tmp/csv/';
    private const CSV_PATH = 'var/csv/';

    public function __construct(string $target, LoggerInterface $logger)
    {
        $this->target = $target;
        $this->id = \time();
    }

    public function secure(): void
    {
        $filesystem = new Filesystem();
        $fileName = $this->getFileName();

        $filesystem->rename(self::CSV_TMP_PATH . $fileName, self::CSV_PATH . $fileName);
    }

    /**
     * Persit data in a file
     *
     * @param string[] $data
     * @return void
     */
    public function persist(array $data): void
    {
        $filesystem = new Filesystem();
        $serializer = new Serializer([new ObjectNormalizer()], [new CsvEncoder()]);
        $fileName = self::CSV_TMP_PATH;
        $fileName .= $this->getFileName();

        $context = [];
        if ($filesystem->exists($fileName)) {
            $context['no_headers'] = true;
        }

        $csv = $serializer->encode($data, 'csv', $context);
        $filesystem->appendToFile($fileName, $csv);
    }

    private function getFileName(): string
    {
        return sprintf('%s_%s.csv', $this->target, $this->id);
    }
}
