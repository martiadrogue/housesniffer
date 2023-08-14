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

            $details = $node->filter('.item-info-container .item-detail-char')->text();
            preg_match('/(\d+ \w+[\p{No}\.]) (\d+ \w+[\p{No}\.]) (.+)|(\d+ \w+[\p{No}\.]) (.+)/u', $details, $match);

            $picture = null;
            $crawlerPicture = $node->filter('.item-multimedia img')->first();
            if ($crawlerPicture->count()) {
                $picture = $crawlerPicture->attr('src');
            }

            $this->logger->notice('Parse house ' . $title);

            return [
                'reference' => $node->attr('data-adid'),
                'url' => $nodeLink->attr('href'),
                'title' => $title,
                'picture' => $picture,
                'price' => $node->filter('.item-info-container .item-price')->text(),
                'details.rooms' => 4 == count($match) ? $match[1] : $match[2],
                'details.space' => 6 == count($match) ? $match[4] : $match[2],
                'details.other' => $match[ count($match) - 1 ],
            ];
        });
    }

    public function seekNextPage(): void
    {
        if ($this->crawler->filter('.next')->count()) {
            $this->operator->update();
        }
    }
}
