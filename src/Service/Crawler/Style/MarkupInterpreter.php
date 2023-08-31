<?php

namespace App\Service\Crawler\Style;

use Psr\Log\LoggerInterface;
use App\Service\Crawler\Translation;
use Symfony\Component\DomCrawler\Crawler;

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

    public function getPageList(string $stream, array $hintMap): array
    {
        $crawler = new Crawler($stream);
        $paginator = $hintMap['paginator'];
        return $crawler->filter($paginator['path'])->reduce(
            function (Crawler $node, $index) use ($paginator): bool {
                $value = $node->extract([$paginator['source']])[0] ?? '';
                if (is_numeric($value)) {
                    return true;
                }

                return false;
            }
        )->each(function (Crawler $node, $index) use ($paginator): int {

            return (int) $node->extract([$paginator['source']])[0];
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
     * @param mixed[] $field
     * @return string
     */
    private function searchPath(Crawler $node, array $field): string
    {
        if ($field['path']) {
            $node = $node->filter($field['path']);
        }

        return $node->extract([$field['source']])[0] ?? '';
    }
}
