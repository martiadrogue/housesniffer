<?php

namespace App\Service\Crawler\Style;

use Psr\Log\LoggerInterface;
use App\Service\Crawler\Translation;
use App\Component\DomCrawler\Crawler;

class MarkupInterpreter implements Interpreter
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function parse(string $stream, array $hintMap): array
    {
        $crawler = new Crawler($stream);

        return $crawler->filter($hintMap['item']['path'])->each(function (Crawler $node) use ($hintMap): array {
            $item = [];
            foreach ($hintMap['fieldList'] as $key => $field) {
                if (empty($field)) {
                    continue;
                }

                $value = $this->searchPath($node, $field);

                $item[$key] = $this->purgeValue($value, $field);
            }

            $this->logger->notice('Parse house ' . $item['title']);

            return $item;
        });
    }

    public function getPageList(string $stream, array $hintMap, int $currentPage): array
    {
        $crawler = new Crawler($stream);
        if (isset($hintMap['next_page'])) {
            $totalPages = $crawler->filter($hintMap['next_page']['path'])->count();
            return $totalPages ? range($currentPage, $currentPage + $totalPages) : [];
        }

        $paginatorHint = $hintMap['paginator'];


        return $crawler->filter($paginatorHint['path'])->reduce(
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
     * @param mixed[] $field
     * @return string
     */
    private function purgeValue(string $value, array $field): string
    {
        if (isset($field['purge'])) {
            $value = preg_replace($field['purge'], '', $value);
        }

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
