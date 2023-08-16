<?php

namespace App\Service\Crawler;

use Psr\Log\LoggerInterface;
use Symfony\Component\Yaml\Yaml;
use App\Service\Crawler\Operator;
use Symfony\Component\DomCrawler\Crawler;

class Parser
{
    private Operator $operator;
    private Crawler $crawler;
    private LoggerInterface $logger;

    public function __construct(string $stream, Operator $operator, LoggerInterface $logger)
    {
        $this->operator = $operator;
        $this->crawler = new Crawler($stream);
        $this->logger = $logger;
    }

    /**
     * Parse the stream of html to an array
     *
     * @return string[]
     */
    public function parse(): array
    {
        $target = $this->operator->getTarget();
        $pathMap = Yaml::parseFile("config/{$target}_item.yml");

        return $this->crawler->filter($pathMap['item'])->each(function (Crawler $node, $index) use ($pathMap): array {
            $nodeLink = $node->filter('.item-link');

            $item = [];
            foreach ($pathMap['fieldList'] as $key => $fullPath) {
                $pathList = explode('@', $fullPath);
                $value = $node->filter($pathList[0])->extract([$pathList[1]])[0] ?? null;

                if (isset($pathList[2])) {
                    $value = preg_replace($pathList[2], '', $value);
                }

                $item[$key] = $value;
            }

            $this->logger->notice('Parse house ' . $item['title']);

            return $item;
        });
    }

    public function seekNextPage(): void
    {
        if ($this->crawler->filter('.next')->count()) {
            $this->operator->update();
        }
    }
}
