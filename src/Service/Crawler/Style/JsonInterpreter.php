<?php

namespace App\Service\Crawler\Style;

use App\Component\JmesPath;
use App\Service\Crawler\Translation;
use Psr\Log\LoggerInterface;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\DomCrawler\Crawler;

class JsonInterpreter implements Interpreter
{
    private LoggerInterface $logger;
    /**
     * Stream parsed
     *
     * @var mixed[]
     */
    private array $dataMap;
    private int $itemCounter;
    private int $size;

    public function __construct(string $stream, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->dataMap = json_decode($stream, true) ?? [];
        $this->itemCounter = 0;
        $this->size = 0;
    }

    public function setStream(string $stream): void
    {
        $this->dataMap = json_decode($stream, true) ?? [];
    }

    public function parse(array $hintList): array
    {
        $itemList = $this->searchPath($this->dataMap, $hintList['item']);
        $this->size = count($itemList);
        $this->itemCounter += $this->size;

        return array_map(function ($element) use ($hintList): array {
            $item = [];
            foreach ($hintList['fieldList'] as $key => $fieldHint) {
                if (empty($fieldHint)) {
                    continue;
                }

                $value = $this->searchPath($element, $fieldHint) ?? '';
                $item[$key] = $this->purgeValue($value, $fieldHint);
            }

            $this->logger->notice('Parse house ' . $item['title']);

            return $item;
        }, $itemList);
    }

    public function getPageList(array $hintList, int $currentPage): array
    {
        [ $currentPage, $totalPages ] = $this->getPageLimits($this->dataMap, $hintList);

        return range($currentPage, $totalPages);
    }

    /**
     * Clean the value
     *
     * @param string $value
     * @param mixed[] $fieldHint
     * @return string
     */
    private function purgeValue(string $value, array $fieldHint): string
    {
        $purgePattern = $fieldHint['purge'] ?? '//';
        $value = preg_replace($purgePattern, '', $value);

        return preg_replace('/\s+/', ' ', trim($value));
    }

    /**
     * Returns the pagination limits
     *
     * @param mixed[] $dataMap
     * @param mixed[] $hintList
     * @return mixed[]
     */
    private function getPageLimits(array $dataMap, array $hintList): array
    {
        if (isset($hintList['total_items'])) {
            $totalItems = intval($this->searchPath($dataMap, $hintList['total_items']));
            $currentPage = $this->itemCounter / $this->size;
            $totalPages = ceil($totalItems / $this->size);

            return [ $currentPage, $totalPages ];
        }

        $currentPage = intval($this->searchPath($dataMap, $hintList['current']));
        $totalPages = intval($this->searchPath($dataMap, $hintList['total_pages']));

        return [ $currentPage, $totalPages ];
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
        return JmesPath\search($field['path'], $element);
    }
}
