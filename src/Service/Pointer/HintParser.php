<?php

namespace App\Service\Pointer;

use App\Component\Yaml\Yaml;
use App\Service\Pointer\Style\Method;
use App\Service\Pointer\HintMiddleware;

class HintParser
{
    private string $target;
    private int $location;
    /**
     * Custom Headers
     *
     * @var string[]
     */
    private array $headerList;
    private int $delay;
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
        $this->location = 1;
        $this->hintMap = [];
    }

    public function setMiddleware(HintMiddleware $middleware): void
    {
        $this->middleware = $middleware;
    }

    /**
     * sets custom headers
     *
     * @param string[] $headerList
     * @return void
     */
    public function setHeaderList(array $headerList): void
    {
        $this->headerList = $headerList;
    }

    public function setDelay(int $delay): void
    {
        $this->delay = $delay;
    }

    public function setLocation(int $location): void
    {
        $this->location = $location;
    }

    public function getLocation(): int
    {
        return $this->location;
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

    /**
     * adds output data
     *
     * @param mixed[] $hintMap
     * @return mixed[]
     */
    private function addOutput(array $hintMap): array
    {
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
