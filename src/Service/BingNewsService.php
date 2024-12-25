<?php

// load and translate news

declare(strict_types=1);

namespace Survos\BingNewsBundle\Service;

use BingNewsSearch\Client;
use BingNewsSearch\Enum\Category;
use BingNewsSearch\Enum\Language;
use BingNewsSearch\Enum\SafeSearch;
use BingNewsSearch\Requests\Request;
use BingNewsSearch\Requests\Search\Get;
use GuzzleHttp\RequestOptions;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\CacheItem;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class BingNewsService
{

    public function __construct(
        private ?string         $apiKey = null,
        private ?string         $endpoint = null,
        private int             $cacheTimeout = 0,
        private ?Client         $client = null,
        private ?CacheInterface $cache = null,
    )
    {
        $this->client = new Client($this->endpoint, $this->apiKey);
        $this->client->enableExceptions(); // throw exceptions for debug
        $this->client->disableSsl(); // disable Guzzle verification SSL
    }

    public function getByCategory(): iterable
    {
        $query = $this->client->category()
            ->get(Category::BUSINESS(), Language::EN_US())
            ->setSafeSearch(SafeSearch::OFF());
        return $this->cachedSearch($query);
    }

    private function cachedSearch(Request $query): iterable
    {
        $request = $query->request();
        dump($request);
        $key = hash('xxh3', serialize($query->getQuery()));
        $news = $this->cache->get($key, function (ItemInterface $item) use ($query) {
            $item->expiresAfter($this->cacheTimeout);
            $request = $query->request();
            return $request->getNews();
        });

        return $news;


    }

    public function searchByKeyword(?string $keyword = null): iterable
    {
        $query = $this->client->search()
            ->get($keyword)
            ->setQuantity(50);
        return $this->cachedSearch($query);
    }


}
