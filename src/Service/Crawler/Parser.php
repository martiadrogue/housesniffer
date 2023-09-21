<?php

namespace App\Service\Crawler;

use App\Service\Crawler\Operator;
use App\Service\Pointer\HintParser;
use App\Service\Crawler\Style\Interpreter;

class Parser
{
    private Operator $operator;
    private Interpreter $interpreter;

    /**
     * List path to the data to scrap
     *
     * @var array<mixed>
     */
    private array $pathMap;

    /**
     * Parse stream of content and return available pages
     *
     * @param Operator $operator
     * @param array $pathMap
     * @param mixed[] $pathMap
     */
    public function __construct(Operator $operator, array $pathMap)
    {
        $this->operator = $operator;
        $this->pathMap = $pathMap;
    }

    public function setStyle(Interpreter $interpreter): void
    {
        $this->interpreter = $interpreter;
    }

    /**
     * Parse the stream of content to an array
     *
     * @return string[]
     */
    public function parse(string $stream): array
    {
        $this->interpreter->setStream($stream);

        return $this->interpreter->parse($this->pathMap);
    }

    public function seekPage(int $currentPage): void
    {
        $pageList = $this->interpreter->getPageList($this->pathMap['page'], $currentPage);

        if (in_array($currentPage, $pageList)) {
            $this->operator->update();
        }
    }
}
