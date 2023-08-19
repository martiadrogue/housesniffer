<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Stopwatch\Stopwatch;

class HintService
{
    private const PATH = "config/hints/";
    private string $target;

    public function __construct(string $target)
    {
        $this->target = $target;
    }

    /**
     * Return hits for the request
     *
     * @param string $target
     * @param integer $page
     * @return mixed[]
     */
    public static function parseHintsRequest(string $target, int $page): array
    {
        $hints = new self($target);

        return  $hints->getHintsRequest($page);
    }

    /**
     * Return hints for the content
     *
     * @param string $target
     * @return mixed[]
     */
    public static function parseHintsContent(string $target): array
    {
        $hints = new self($target);

        return $hints->getHintsContent();
    }

    /**
     * Start the performance measurement
     *
     * @return mixed[]
     */
    private function getHintsContent(): array
    {
        return Yaml::parseFile(sprintf(self::PATH . "%s_item.yml", $this->target));
    }

    /**
     * Start the performance measurement
     *
     * @param integer $currentPage
     * @return mixed[]
     */
    private function getHintsRequest(int $currentPage): array
    {
        $data = Yaml::parseFile(sprintf(self::PATH . "%s.yml", $this->target));
        $data['query']['page'] = $this->mutatePage($data, $currentPage);
        $data['url'] = $this->prepareUrl($data['url'], $data['query']);

        return $data;
    }

    /**
     * Format data with real values
     *
     * @param string $url
     * @param string[] $queryMap
     * @return string
     */
    private function prepareUrl(string $url, array $queryMap): string
    {
        $query = '';
        foreach ($queryMap as $key => $value) {
            if (str_contains($url, "{{$key}}")) {
                $url = preg_replace("/{($key)}/", $value, $url);
                continue;
            }

            $query .= sprintf('&%s=%s', $key, $value);
        }

        return sprintf('%s?%s', $url, $query);
    }

    /**
     * Return the mutator for the url page, there are 2 mutators; position and
     * index. Position is the default mutator. Index is less one unit relative
     * to position.
     *
     * @param array<mixed> $data
     * @param integer $number
     * @return integer
     */
    private function mutatePage(array $data, int $number): int
    {
        $mutatorSchema = $data['parameters']['schema'] ?? 'position';
        $mutator = 'index' == $mutatorSchema ? -1 : 0;

        return $number + $mutator;
    }
}
