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
    private Translation $translation;

    /**
     * List path to the data to scrap
     *
     * @var array<mixed>
     */
    private array $pathMap;

    public function __construct(Operator $operator)
    {
        $this->operator = $operator;

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
    public function parse(string $stream): array
    {
        return $this->translation->parse($stream, $this->pathMap);
    }

    public function seekPage(string $stream, int $currentPage): void
    {
        $pageList = $this->translation->seekPage($stream, $this->pathMap['page']);

        if (in_array($currentPage, $pageList)) {
            $this->operator->update();
        }
    }
}
