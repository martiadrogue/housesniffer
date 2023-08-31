<?php

namespace App\Service\Pointer\Style;

use App\Service\Pointer\HintParser;

class RequestMutator implements Method
{
    private HintParser $server;

    public function __construct(HintParser $server)
    {
        $this->server = $server;
    }

    public function process(array $hintSet, string $target): array
    {
        $hintSet['parameters'][0] = $this->mutatePage($hintSet['parameters'][0], $this->server->getPage());
        $hintSet = $this->mutateInput($hintSet);
        $hintSet['url'] = $this->mutateUrl($hintSet['url'], $hintSet['query']);

        return $hintSet;
    }

    /**
     * Format data with real values
     *
     * @param string $url
     * @param string[] $queryMap
     * @return string
     */
    private function mutateUrl(string $url, array $queryMap): string
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
    private function mutateInput(array $data): array
    {
        foreach ($data['parameters'] as $parameter) {
            $data['url'] = preg_replace("/{({$parameter['name']})}/", $parameter['value'], $data['url']);
        }

        foreach ($data['query'] as $key => $value) {
            $value = $value ?? '';
            foreach ($data['parameters'] as $parameter) {
                $data['query'][$key] = preg_replace("/{({$parameter['name']})}/", $parameter['value'], $value);
            }
        }

        return $data;
    }
}
