<?php

namespace App\Service\Crawler\Style;

use Psr\Log\LoggerInterface;
use Symfony\Component\Yaml\Yaml;
use App\Service\Crawler\Translation;
use Symfony\Component\DomCrawler\Crawler;

class JsonInterpreter implements Interpreter
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function parse(string $stream, array $pathMap): array
    {
        $data = json_decode($stream, true);

        return array_map(function ($element) use ($pathMap): array {
            $item = [];
            foreach ($pathMap['fieldList'] as $path) {
                $key = array_key_first($path);
                $value = $this->searchPath($path[$key], $element);
                $item[$key] = preg_replace('/\s+/', ' ', trim($value));
            }

            $this->logger->notice('Parse house ' . $item['title']);

            return $item;
        }, $this->searchPath($pathMap['item'], $data));
    }

    public function getPageList(string $stream, array $hintList): array
    {
        $data = json_decode($stream, true);
        $currentPage = intval($this->searchPath($hintList['current'], $data));
        $totalPages = intval($this->searchPath($hintList['total'], $data));

        return range($currentPage, $totalPages);
    }

    /**
     * Undocumented function
     *
     * @param string $path
     * @param mixed[] $element
     * @return mixed
     */
    private function searchPath(string $path, array $element): mixed
    {
        return \jmespath\search($path, $element);
    }
}
