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
     * Get hints for content
     *
     * @return mixed[]
     */
    private function getHintsContent(): array
    {
        return Yaml::parseFile(sprintf(self::PATH . "%s_item.yml", $this->target));
    }

    /**
     * Get hints for requests
     *
     * @param integer $currentPage
     * @return mixed[]
     */
    private function getHintsRequest(int $currentPage): array
    {
        $data = Yaml::parseFile(sprintf(self::PATH . "%s.yml", $this->target));
        $data['parameters'][0] = $this->mutatePage($data['parameters'][0], $currentPage);
        $data = $this->fillGaps($data);
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
        $query = http_build_query($queryMap);

        return sprintf('%s?%s', $url, $query);
    }

    /**
     * Return the mutator for the page's url, there are 2 mutators; position and
     * index. Position is the default mutator. Index is less one unit relative
     * to position.
     *
     * @param array<mixed> $data
     * @param integer $number
     * @return array<mixed>
     */
    private function mutatePage(array $data, int $number): array
    {
        $mutatorSchema = $data['schema'] ?? 'position';
        $data['value'] = 'index' == $mutatorSchema ? -1 : 0;
        $data['value'] += $number;

        return $data;
    }

    /**
     * Fill the gaps of the hints
     *
     * @param array<mixed> $data
     * @return array<mixed>
     */
    private function fillGaps(array $data): array
    {
        foreach ($data['parameters'] as $parameter) {
            $data['url'] = preg_replace("/{({$parameter['name']})}/", $parameter['value'], $data['url']);
        }

        foreach ($data['query'] as $key => $value) {
            foreach ($data['parameters'] as $parameter) {
                $data['query'][$key] = preg_replace("/{({$parameter['name']})}/", $parameter['value'], $value);
            }
        }

        return $data;
    }
}
