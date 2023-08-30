<?php

namespace App\Service\Crawler\Style;

use Psr\Log\LoggerInterface;
use Symfony\Component\Yaml\Yaml;
use App\Service\Crawler\Translation;
use Symfony\Component\DomCrawler\Crawler;

class JsonInterpreter implements Interpreter
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function parse(string $stream, array $hintList): array
    {
        $dataMap = json_decode($stream, true);
        return array_map(function ($element) use ($hintList): array {
            $item = [];
            foreach ($hintList['fieldList'] as $key => $field) {
                if (empty($field)) {
                    continue;
                }

                $value = $this->searchPath($element, $field) ?? '';
                $item[$key] = preg_replace('/\s+/', ' ', trim($value));
            }

            $this->logger->notice('Parse house ' . $item['title']);

            return $item;
        }, $this->searchPath($dataMap, $hintList['item']));
    }

    public function getPageList(string $stream, array $hintList): array
    {
        $dataMap = json_decode($stream, true);
        $currentPage = intval($this->searchPath($dataMap, $hintList['current']));
        $totalPages = intval($this->searchPath($dataMap, $hintList['total']));

        return range($currentPage, $totalPages);
    }

    /**
     * Get content from given path
     *
     * @param mixed[] $element
     * @param mixed[] $field
     * @return mixed
     */
    private function searchPath(array $element, array $field): mixed
    {
        return \jmespath\search($field['path'], $element);
    }
}
