<?php

namespace App\Service\Crawler\Style;

use Psr\Log\LoggerInterface;
use App\Service\Crawler\Translation;
use App\Component\DomCrawler\Crawler;

class MarkupInterpreter implements Interpreter
{
    private LoggerInterface $logger;
    private Crawler $crawler;

    public function __construct(string $stream, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->crawler = new Crawler($stream);
    }

    public function parse(array $hintMap): array
    {
        return $this->crawler->filter($hintMap['item']['path'])->each(function (Crawler $node) use ($hintMap): array {
            $hintMap['fieldList'] = array_filter($hintMap['fieldList'], function (?array $fieldHint): bool {
                return isset($fieldHint);
            });

            $item = [];
            foreach ($hintMap['fieldList'] as $key => $field) {
                $value = $this->searchPath($node, $field);
                $item[$key] = $this->purgeValue($value, $field);
            }

            $this->logger->notice('Parse house ' . $item['title']);

            return $item;
        });
    }

    public function getPageList(array $hintMap, int $currentPage): array
    {
        if (isset($hintMap['next_page'])) {
            $totalPages = $this->crawler->filter($hintMap['next_page']['path'])->count();

            return $totalPages ? range($currentPage, $currentPage + $totalPages) : [];
        }

        $paginatorHint = $hintMap['paginator'];

        return $this->crawler->filter($paginatorHint['path'])->reduce(
            function (Crawler $node, $index) use ($paginatorHint): bool {
                $value = $node->extractFirst([$paginatorHint['source']]);

                if (is_numeric($value)) {
                    return true;
                }

                return false;
            }
        )->each(function (Crawler $node, $index) use ($paginatorHint): int {

            return (int) $node->extractFirst([$paginatorHint['source']]);
        });
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
     * Get content from given path
     *
     * @param Crawler $node
     * @param mixed[] $fieldHint
     * @return string
     */
    private function searchPath(Crawler $node, array $fieldHint): string
    {
        if ($fieldHint['path']) {
            $node = $node->filter($fieldHint['path']);
        }

        return $node->extractFirst([$fieldHint['source']]);
    }
}
