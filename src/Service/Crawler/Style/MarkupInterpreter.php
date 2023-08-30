<?php

namespace App\Service\Crawler\Style;

use Psr\Log\LoggerInterface;
use Symfony\Component\Yaml\Yaml;
use App\Service\Crawler\Translation;
use Symfony\Component\DomCrawler\Crawler;

class MarkupInterpreter implements Interpreter
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function parse(string $stream, array $hintList): array
    {
        $crawler = new Crawler($stream);

        return $crawler->filter($hintList['item']['path'])->each(function (Crawler $node, $index) use ($hintList): array {
            $item = [];
            foreach ($hintList['fieldList'] as $key => $field) {
                if (empty($field)) {
                    continue;
                }

                $value = $this->searchPath($node, $field);

                if (isset($field['purge'])) {
                    $value = preg_replace($field['purge'], '', $value);
                }

                $item[$key] = preg_replace('/\s+/', ' ', trim($value));
            }

            $this->logger->notice('Parse house ' . $item['title']);

            return $item;
        });
    }

    public function getPageList(string $stream, array $hintList): array
    {
        $crawler = new Crawler($stream);
        $paginator = $hintList['paginator'];
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

    private function searchPath(Crawler $node, array $field): string
    {
        if ($field['path']) {
            return $node->filter($field['path'])->extract([$field['source']])[0] ?? '';
        }

        return $node->extract([$field['source']])[0] ?? '';
    }
}
