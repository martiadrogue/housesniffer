<?php

namespace App\Service\Crawler;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\CacheInterface;
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
     * @return string
     */
    public function fetchList(int $currentPage): string
    {
        return $this->cache->get(
            'idealista_list_' . $currentPage,
            function (ItemInterface $item) use ($currentPage): string {
                $item->expiresAfter(3600 * 24);

                $url = 'https://www.idealista.com';
                $url .= "/alquiler-viviendas/barcelona-barcelona/pagina-{$currentPage}.htm";
                $url .= '?' . http_build_query([
                    'ordenado-por' => 'fecha-publicacion-desc',
                ]);
                $userAgent = 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:109.0) Gecko/20100101 Firefox/115.0';
                $response = $this->client->request('GET', $url, [
                    'headers' => [
                        'User-Agent' => $userAgent,
                        'Accept-Language' => 'en-US,en;q=0.5',
                    ],
                ]);
                $content = $response->getContent();
                $this->logger->info('well done');

                return $content;
            }
        );
    }
}
