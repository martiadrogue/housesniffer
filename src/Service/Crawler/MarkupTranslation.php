<?php

namespace App\Service\Crawler;

use Psr\Log\LoggerInterface;
use Symfony\Component\Yaml\Yaml;
use App\Service\Crawler\Translation;
use Symfony\Component\DomCrawler\Crawler;

class MarkupTranslation implements Translation
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function parse(Crawler $crawler, array $pathMap): array
    {
        return $crawler->filter($pathMap['item'])->each(function (Crawler $node, $index) use ($pathMap): array {
            $nodeLink = $node->filter('.item-link');

            $item = [];
            foreach ($pathMap['fieldList'] as $key => $path) {
                $hintList = explode('@', $path);
                $value = $node->filter($hintList[0])->extract([$hintList[1]])[0] ?? '';

                if (isset($hintList[2])) {
                    $value = preg_replace($hintList[2], '', $value);
                }

                $item[$key] = preg_replace('/\s+/', ' ', trim($value));
            }

            $this->logger->notice('Parse house ' . $item['title']);

            return $item;
        });
    }

    public function seekPage(Crawler $crawler, array $hintList): array
    {
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