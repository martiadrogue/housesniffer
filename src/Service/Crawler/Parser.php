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
     * @param Interpreter $interpreter
     * @param mixed[] $pathMap
     */
    public function __construct(Operator $operator, Interpreter $interpreter, array $pathMap)
    {
        $this->operator = $operator;
        $this->interpreter = $interpreter;

        $this->pathMap = $pathMap;
    }

    /**
     * Parse the stream of content to an array
     *
     * @return string[]
     */
    public function parse(string $stream): array
    {
        return $this->interpreter->parse($stream, $this->pathMap);
    }

    public function seekPage(string $stream, int $currentPage): void
    {
        $pageList = $this->interpreter->getPageList($stream, $this->pathMap['page']);

        if (in_array($currentPage, $pageList)) {
            $this->operator->update();
        }
    }
}
