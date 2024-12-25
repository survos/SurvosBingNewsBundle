<?php

// load and translate news

declare(strict_types=1);

namespace Survos\BingNewsBundle\Service;

use BingNewsSearch\Client;
use Guardian\Entity\APIEntity;
use Guardian\Entity\Content;
use Guardian\Entity\ContentAPIEntity;
use Guardian\Entity\Tags;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\CacheItem;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class BingNewsService
{

    public function __construct(
    )
    {
        $endpoint = 'https://api.bing.microsoft.com/';
        $accessKey = '9b527e84cfe14dbfa612b79c47b9bed3';

    }


    public function __construct(
        private ?string $apiKey=null,
        private ?string $endpoint=null,
        private int $cacheTimeout = 0,
        private ?Client $client=null,
        private ?CacheInterface $cache=null,
    )

    {
        $client = new Client($endpoint, $apiKey);
        $client->enableExceptions(); // throw exceptions for debug
        $client->disableSsl(); // disable Guzzle verification SSL
        $this->client = $client;
    }

    public function fetch(APIEntity $apiEntity)
    {

        ($r = new \ReflectionMethod($apiEntity, 'buildUrl'))
            ->setAccessible(true);
        $url = $r->invoke($apiEntity);
        $key = hash('xxh3', $url);
        $response = $this->cache->get($key, function(ItemInterface $item) use ($url, $apiEntity)
        {
            $item->expiresAfter($this->cacheTimeout);
            $results = $apiEntity->makeApiCall($url);
            return json_decode($results)->response;
        } );
        return $response;
    }


    public function content(?string $q=null)
    {

    }

    public function contentApi(): Content
    {
        return $this->guardianAPI->content();
    }

    public function tagsApi(): Tags
    {
        return $this->guardianAPI->tags();
    }



}
