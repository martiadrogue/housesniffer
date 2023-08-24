<?php

namespace App\Service\Crawler\Style;

use Psr\Log\LoggerInterface;
use Symfony\Component\DomCrawler\Crawler;

interface Interpreter
{
    /**
     * Parse the stream of html to an array
     *
     * @param string $stream
     * @param array<mixed> $pathMap
     * @return array<mixed>
     */
    public function parse(string $stream, array $pathMap): array;

    /**
     * Get all visible pages
     *
     * @param string $stream
     * @param array<mixed> $hintList
     * @return array<int>
     */
    public function getPageList(string $stream, array $hintList): array;
}
