<?php

namespace App\Service\Crawler\Style;

use Psr\Log\LoggerInterface;
use Symfony\Component\DomCrawler\Crawler;

interface Interpreter
{
    /**
     * Parse the stream of html to an array
     *
     * @param array<mixed> $pathMap
     * @return array<mixed>
     */
    public function parse(array $pathMap): array;

    /**
     * Get all visible pages
     *
     * @param array<mixed> $hintList
     * @return array<int>
     */
    public function getPageList(array $hintList, int $currentPage): array;
}
