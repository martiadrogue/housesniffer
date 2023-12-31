<?php

namespace App\Service\Crawler;

use App\Service\Pointer\HintParser;
use App\Service\Pointer\HintService;
use Psr\Log\LoggerInterface;
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
    private string $previousContentHash;

    public function __construct(HttpClientInterface $client, CacheInterface $cache, LoggerInterface $logger)
    {
        $this->client = $client;
        $this->cache = $cache;
        $this->logger = $logger;
        $this->previousContentHash = '';
    }

    /**
     * Fetch house information from a target
     *
     * @param HintParser $hintProcesor
     * @return string
     */
    public function fetch(HintParser $hintProcesor): string
    {
        $hintMap = $hintProcesor->parse();
        return $this->cache->get(
            sprintf('%s_%s', $hintMap['method'], $hintMap['url']),
            function (ItemInterface $item) use ($hintMap): string {
                sleep($hintMap['delay']);
                $item->expiresAfter(3600 * 24);

                $response = $this->client->request($hintMap['method'], $hintMap['url'], [
                    'headers' => $hintMap['headers'],
                ]);

                if (isset($hintMap['error']) && $hintMap['error'] == $response->getStatusCode()) {
                    throw new Exception('Response status code is different than expected.');
                }

                $content = $response->getContent();
                if (empty($content)) {
                    throw new Exception('The content is empty.');
                }

                $contentHash = hash('sha256', $content);
                if ($contentHash == $this->previousContentHash) {
                    throw new Exception("Content isn't up to date.");
                }
                $this->previousContentHash = $contentHash;

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
