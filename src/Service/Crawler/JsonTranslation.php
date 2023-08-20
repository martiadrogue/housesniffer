<?php

namespace App\Service\Crawler;

use Psr\Log\LoggerInterface;
use Symfony\Component\Yaml\Yaml;
use App\Service\Crawler\Translation;
use Symfony\Component\DomCrawler\Crawler;

class JsonTranslation implements Translation
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function parse(string $stream, array $pathMap): array
    {
        $data = json_decode($stream, true);

        return array_map(function ($element) use ($pathMap): array {
            $item = [];
            foreach ($pathMap['fieldList'] as $key => $path) {
                $value = \jmespath\search($path, $element) ?? '';
                $item[$key] = preg_replace('/\s+/', ' ', trim($value));
            }

            $this->logger->notice('Parse house ' . $item['title']);

            return $item;
        }, \jmespath\search($pathMap['item'], $data));
    }

    public function seekPage(string $stream, array $hintList): array
    {
        $data = json_decode($stream, true);
        $currentPage = \jmespath\search($hintList['current'], $data);
        $totalPages = \jmespath\search($hintList['total'], $data);

        return range($currentPage, $totalPages);
    }
}
