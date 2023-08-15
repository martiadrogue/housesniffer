<?php

namespace App\Service\Crawler;

use Psr\Log\LoggerInterface;
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
        return $this->crawler->filter('.extended-item')->each(function (Crawler $node, $index): array {
            $nodeLink = $node->filter('.item-link');
            $title = $nodeLink->attr('title');
            $detailsMap = $this->parseDetails($node);

            $this->logger->notice('Parse house ' . $title);

            return [
                'reference' => $node->attr('data-adid'),
                'url' => $nodeLink->attr('href'),
                'title' => $title,
                'picture' => $this->parsePicture($node),
                'price' => $node->filter('.item-info-container .item-price')->text(),
                'rooms' => $detailsMap['rooms'],
                'space' => $detailsMap['space'],
                'floor' => $detailsMap['floor'],
            ];
        });
    }

    public function seekNextPage(): void
    {
        if ($this->crawler->filter('.next')->count()) {
            $this->operator->update();
        }
    }

    private function parsePicture(Crawler $node): string
    {
        $crawlerPicture = $node->filter('.item-multimedia img')->first();
        if ($crawlerPicture->count()) {
            return $crawlerPicture->attr('src');
        }

        return '';
    }

    /**
     * Parse item details
     *
     * @param Crawler $node
     * @return array<string, string>
     */
    private function parseDetails(Crawler $node): array
    {
        $detailsMap = [
            'rooms' => '/(\d+\shab.)/u',
            'space' => '/(\d+\sm[\p{No}])/u',
            'floor' => '/(.+ascensor$)/u',
        ];

        $detailsList = $node->filter('.item-info-container .item-detail')->each(
            function (Crawler $nodeDetails, $index): string {
                return $nodeDetails->text();
            }
        );

        foreach ($detailsMap as $key => $expression) {
            $detailsMap[$key] = '';
            foreach ($detailsList as $value) {
                if (preg_match($expression, $value, $match)) {
                    $detailsMap[$key] = $match[1];
                    break;
                }
            }
        }

        return $detailsMap;
    }
}
