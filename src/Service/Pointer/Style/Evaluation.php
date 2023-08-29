<?php

namespace App\Service\Pointer\Style;

use Psr\Log\LoggerInterface;
use Symfony\Component\DomCrawler\Crawler;

interface Evaluation
{
    /**
     * Parse the stream of content to an array
     *
     * @param mixed[] $hintSet
     * @param string $target
     * @return boolean
     */
    public function process(array $hintSet, string $target): bool;
}
