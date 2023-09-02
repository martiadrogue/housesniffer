<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Component\DomCrawler;

use Masterminds\HTML5;
use Symfony\Component\DomCrawler;

class Crawler extends DomCrawler\Crawler
{
    /**
     * Extracts first information from the list of nodes. And return and empty
     * string if anything is found.
     *
     * You can extract attributes or/and the node value (_text).
     *
     * Example:
     *
     *     $crawler->filter('h1 a')->extractFirst(['_text', 'href']);
     *
     * @param string[] $attributes
     * @return string
     */
    public function extractFirst(array $attributes): string
    {
        $data = parent::extract($attributes);

        return $data[0] ?? '';
    }
}
