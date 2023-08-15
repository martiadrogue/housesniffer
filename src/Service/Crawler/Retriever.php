<?php

namespace App\Service\Crawler;

use Psr\Log\LoggerInterface;
use Symfony\Component\Yaml\Yaml;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class Retriever
{
    private HttpClientInterface $client;
    private CacheInterface $cache;
    private LoggerInterface $logger;

    public function __construct(HttpClientInterface $client, CacheInterface $cache, LoggerInterface $logger)
    {
        $this->client = $client;
        $this->cache = $cache;
        $this->logger = $logger;
    }

    /**
     * Fetch the house list information from idealista
     *
     * @param string $name
     * @param integer $page
     * @return string
     */
    public function fetchList(string $name, int $page): string
    {
        $data = Yaml::parseFile("config/{$name}.yml");
        $data['query']['page'] = $page;
        $data['url'] = $this->prepareUrl($data['url'], $data['query']);

        return $this->cache->get(
            sprintf('%s_%s', $name, $page),
            function (ItemInterface $item) use ($data): string {
                $item->expiresAfter(3600 * 24);

                $response = $this->client->request($data['method'], $data['url'], [
                    'headers' => $data['headers'],
                ]);
                $content = $response->getContent();

                $this->logResult($response);

                return $content;
            }
        );
    }

    private function logResult(ResponseInterface $response): void
    {
        $info = $response->getInfo();

        $message = "Response data: Time {$info['total_time']}, ";
        $message .= "Size {$info['size_download']}, ";
        $message .= "Speed {$info['speed_download']}, ";
        $message .= "Ip {$info['primary_ip']}:{$info['primary_port']}";

        $this->logger->info($message);
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
}
