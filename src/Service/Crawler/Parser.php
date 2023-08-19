<?php

namespace App\Service\Crawler;

use App\Service\HintService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Yaml\Yaml;
use App\Service\Crawler\Operator;
use Symfony\Component\DomCrawler\Crawler;

class Parser
{
    private Operator $operator;
    private Crawler $crawler;
    private LoggerInterface $logger;

    /**
     * List path to the data to scrap
     *
     * @var array<mixed>
     */
    private array $pathMap;

    public function __construct(string $stream, Operator $operator, LoggerInterface $logger)
    {
        $this->operator = $operator;
        $this->crawler = new Crawler($stream);
        $this->logger = $logger;

        $target = $this->operator->getTarget();
        $this->pathMap = HintService::parseHintsContent($target);
    }

    /**
     * Parse the stream of html to an array
     *
     * @return string[]
     */
    public function parse(): array
    {

        return $this->crawler->filter($this->pathMap['item'])->each(function (Crawler $node, $index): array {
            $nodeLink = $node->filter('.item-link');

            $item = [];
            foreach ($this->pathMap['fieldList'] as $key => $path) {
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

    public function seekPage(int $currentPage): void
    {
        $hintList = explode('@', $this->pathMap['page']);
        $pageList = $this->crawler->filter($hintList[0])->reduce(function (Crawler $node, $index) use ($hintList): bool {
            $value = $node->extract([$hintList[1]])[0] ?? '';
            if (is_numeric($value)) {
                return true;
            }

            return false;
        })->each(function (Crawler $node, $index) use ($hintList): int {

            return (int) $node->extract([$hintList[1]])[0];
        });

        if (in_array($currentPage, $pageList)) {
                $this->operator->update();
        }
    }
}
