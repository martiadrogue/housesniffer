<?php

namespace App\Service\Crawler;

use App\Service\HintService;
use Symfony\Component\Yaml\Yaml;
use App\Service\Crawler\Operator;
use App\Service\Crawler\Translation;
use Symfony\Component\DomCrawler\Crawler;

class Parser
{
    private Operator $operator;
    private Crawler $crawler;
    private Translation $translation;

    /**
     * List path to the data to scrap
     *
     * @var array<mixed>
     */
    private array $pathMap;

    public function __construct(string $stream, Operator $operator)
    {
        $this->operator = $operator;
        $this->crawler = new Crawler($stream);

        $target = $this->operator->getTarget();
        $this->pathMap = HintService::parseHintsContent($target);
    }

    public function setTranslation(Translation $translation): void
    {
        $this->translation = $translation;
    }

    /**
     * Parse the stream of html to an array
     *
     * @return string[]
     */
    public function parse(): array
    {
        return $this->translation->parse($this->crawler, $this->pathMap);
    }

    public function seekPage(int $currentPage): void
    {
        $hintList = explode('@', $this->pathMap['page']);
        $pageList = $this->translation->seekPage($this->crawler, $hintList);

        if (in_array($currentPage, $pageList)) {
            $this->operator->update();
        }
    }
}
