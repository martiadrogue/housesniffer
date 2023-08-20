<?php

namespace App\Service\Crawler;

use App\Service\HintService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Yaml\Yaml;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Config\Definition\Exception\Exception;

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
     * @param string $target
     * @param integer $page
     * @return string
     */
    public function fetchList(string $target, int $page): string
    {
        $data = HintService::parseHintsRequest($target, $page);

        return $this->cache->get(
            sprintf('%s_%s', $target, $page),
            function (ItemInterface $item) use ($data): string {
                $item->expiresAfter(3600 * 24);

                $response = $this->client->request($data['method'], $data['url'], [
                    'headers' => $data['headers'],
                ]);
                $content = $response->getContent();

                if ($data['error'] == $response->getStatusCode()) {
                    throw new Exception('Response status code is different than expected.');
                }

                $this->logResult($response);

                return $content;
            }
        );
    }

    private function logResult(ResponseInterface $response): void
    {
        $info = $response->getInfo();

        $message = "Response data: Start Time {$info['start_time']}, ";
        $message .= "Time {$info['total_time']}, ";
        $message .= "Connect Time {$info['connect_time']}, ";
        $message .= "Request Size {$info['request_size']}, ";
        $message .= "Size Download {$info['size_download']}, ";
        $message .= "Ip {$info['primary_ip']}:{$info['primary_port']}";

        $this->logger->info($message);
    }
}
