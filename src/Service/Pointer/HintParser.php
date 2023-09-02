<?php

namespace App\Service\Pointer;

use App\Component\Yaml\Yaml;
use App\Service\Pointer\Style\Method;
use App\Service\Pointer\HintMiddleware;

class HintParser
{
    private string $target;
    private int $page;
    /**
     * Hints to parse to content
     *
     * @var mixed[]
     */
    private array $hintMap;
    private HintMiddleware $middleware;

    private const PATH = "config/hints/";

    public function __construct(string $target)
    {
        $this->target = $target;
        $this->hintMap = [];
    }

    public function setMiddleware(HintMiddleware $middleware): void
    {
        $this->middleware = $middleware;
    }

    public function setPage(int $page): void
    {
        $this->page = $page;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * Get hints for requests
     *
     * @return mixed[]
     */
    public function parse(): array
    {
        if (empty($this->hintMap)) {
            $this->hintMap = Yaml::parseFile(sprintf(self::PATH . "%s.yml", $this->target));
        }

        return $this->middleware->check($this->hintMap);
    }
}
