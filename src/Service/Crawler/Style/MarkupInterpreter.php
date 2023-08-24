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

    public function parse(string $stream, array $pathMap): array
    {
        $crawler = new Crawler($stream);

        return $crawler->filter($pathMap['item'])->each(function (Crawler $node, $index) use ($pathMap): array {
            $item = [];
            foreach ($pathMap['fieldList'] as $path) {
                $key = array_key_first($path);
                $hintList = explode('@', $path[$key]);
                $value = $node->filter($hintList[0])->extract([$hintList[1]])[0] ?? '';

                if (isset($path['sanitize'])) {
                    $value = preg_replace($path['sanitize'], '', $value);
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
        $hintList = explode('@', $hintList['paginator']);

        return $crawler->filter($hintList[0])->reduce(
            function (Crawler $node, $index) use ($hintList): bool {
                $value = $node->extract([$hintList[1]])[0] ?? '';
                if (is_numeric($value)) {
                    return true;
                }

                return false;
            }
        )->each(function (Crawler $node, $index) use ($hintList): int {

            return (int) $node->extract([$hintList[1]])[0];
        });
    }
}
