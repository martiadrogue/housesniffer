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

    public function setHeaderList(array $headerList): void
    {
        $this->headerList = $headerList;
    }

    public function setDelay(int $delay): void
    {
        $this->delay = $delay;
    }

    public function setRender(bool $render): void
    {
        $this->render = $render;
    }

    public function setProxy(string $proxy): void
    {
        $this->proxy = $proxy;
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
            $hintMap = Yaml::parseFile(sprintf(self::PATH . "%s.yml", $this->target));
            $this->hintMap = $this->addOutput($hintMap);
        }

        return $this->middleware->check($this->hintMap);
    }

    private function addOutput(array $hintMap): array
    {
        if (isset($this->headerList)) {
            $hintMap['headers'] = $this->headerList;
        }

        if (isset($this->delay)) {
            $hintMap['delay'] = $this->delay;
        }

        if (isset($this->headerList)) {
            foreach ($this->headerList as $key => $value) {
                $hintMap['headers'][$key] = $value;
            }
        }

        if (isset($this->proxy)) {
            $hintMap['proxy'] = $this->proxy;
        }

        return $hintMap;
    }
}
