<?php

namespace App\Service\Crawler;

use Psr\Log\LoggerInterface;
use Symfony\Component\DomCrawler\Crawler;

interface Translation
{
    /**
     * Parse the stream of html to an array
     *
     * @param Crawler $crawler
     * @param array<mixed> $pathMap
     * @return array<mixed>
     */
    public function parse(Crawler $crawler, array $pathMap): array;

    /**
     * Get all visible pages
     *
     * @param Crawler $crawler
     * @param array<mixed> $hintList
     * @return array<int>
     */
    public function seekPage(Crawler $crawler, array $hintList): array;
}
