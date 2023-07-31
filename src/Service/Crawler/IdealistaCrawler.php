<?php

namespace App\Service\Crawler;

use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class IdealistaCrawler
{
    private HttpClientInterface $client;
    private CacheInterface $cache;
    private LoggerInterface $logger;

    private string $fileName;
    private int $currentPage;

    private const CSV_PATH = 'var/csv/homes';

    public function __construct(HttpClientInterface $client, CacheInterface $cache, LoggerInterface $logger)
    {
        $this->client = $client;
        $this->cache = $cache;
        $this->logger = $logger;

        $this->fileName = self::CSV_PATH . '_' . Uuid::v4() . '.csv';
        $this->currentPage = 1;
    }

    public function parseList(): void
    {
        $houseStream = $this->fetchList();

        $houseList = $this->parseContent($houseStream);

        $this->persistData($houseList);

        $crawler = new Crawler($houseStream);
        if ($crawler->filter('.next')->count()) {
            $this->currentPage += 1;
            $this->parseList();
        }
    }

    /**
     * Fetch the house list information from idealista
     *
     * @return string
     */
    private function fetchList(): string
    {
        return $this->cache->get('idealista_list_' . $this->currentPage, function (ItemInterface $item): string {
            $item->expiresAfter(3600);

            $domain = 'https://www.idealista.com';
            $path = "/alquiler-viviendas/barcelona-barcelona/pagina-{$this->currentPage}.htm";
            $params =  http_build_query([
                'ordenado-por' => 'fecha-publicacion-desc',
            ]);

            $response = $this->client->request('GET', $domain . $path . '?' . $params, [
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:109.0) Gecko/20100101 Firefox/115.0',
                    'Accept-Language' => 'en-US,en;q=0.5',
                ],
            ]);

            $content = $response->getContent();

            return $content;
        });
    }

    /**
     * Persit data in a file
     *
     * @param string[] $data
     * @return void
     */
    private function persistData(array $data): void
    {
        $context = [];
        $serializer = new Serializer([new ObjectNormalizer()], [new CsvEncoder()]);
        $filesystem = new Filesystem();

        if ($filesystem->exists($this->fileName)) {
            $context['no_headers'] = true;
        }

        $csv = $serializer->encode($data, 'csv', $context);

        $filesystem->appendToFile($this->fileName, $csv);
    }

    /**
     * Parse the stream of html to an array
     *
     * @param string $content
     * @return string[]
     */
    private function parseContent(string $content): array
    {
        $crawler = new Crawler($content);

        return $crawler->filter('.extended-item')->each(function (Crawler $node, $index): array {
            $nodeLink = $node->filter('.item-link');
            $title = $nodeLink->attr('title');

            $details = $node->filter('.item-info-container .item-detail-char')->text();
            preg_match('/(\d+ \w+[\p{No}\.]) (\d+ \w+[\p{No}\.]) (.+)|(\d+ \w+[\p{No}\.]) (.+)/u', $details, $match);

            $picture = null;
            $crawlerPicture = $node->filter('.item-multimedia img')->first();
            if ($crawlerPicture->count()) {
                $picture = $crawlerPicture->attr('src');
            }

            $this->logger->notice('Parse house ' . $title);

            return [
                'reference' => $node->attr('data-adid'),
                'url' => $nodeLink->attr('href'),
                'title' => $title,
                'picture' => $picture,
                'price' => $node->filter('.item-info-container .item-price')->text(),
                'details.rooms' => 4 == count($match) ? $match[1] : $match[2],
                'details.space' => 6 == count($match) ? $match[4] : $match[2],
                'details.other' => $match[ count($match) - 1 ],
            ];
        });
    }
}
