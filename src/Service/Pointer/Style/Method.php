<?php

namespace App\Service\Pointer\Style;

use Psr\Log\LoggerInterface;
use Symfony\Component\DomCrawler\Crawler;

interface Method
{
    /**
     * Parse the stream of html to an array
     *
     * @param array<mixed> $hintSet
     * @return array<mixed>
     */
    public function process(array $hintSet, string $target): array;
}
