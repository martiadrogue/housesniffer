<?php

namespace App\Service\Pointer;

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
    private Method $tactic;

    public function __construct(Method $tactic, string $target)
    {
        $this->tactic = $tactic;
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
        if (!$this->hintMap) {
            $this->hintMap = $this->tactic->process($this->hintMap, $this->target);
        }

        return $this->middleware->check($this->hintMap);
    }
}
