<?php

// load and translate news

declare(strict_types=1);

namespace Survos\BingNewsBundle\Service;

use BingNewsSearch\Client;
use BingNewsSearch\Enum\Category;
use BingNewsSearch\Enum\Language;
use BingNewsSearch\Enum\SafeSearch;
use BingNewsSearch\Enum\SortBy;
use BingNewsSearch\Requests\Request;
use BingNewsSearch\Requests\Search\Get;
use BingNewsSearch\Structs\NewsAnswer;
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
        private CacheInterface $cache,
        private ?string         $apiKey = null,
        protected ?string         $endpoint = null,
        private int             $cacheTimeout = 0,
        private ?Client         $client = null,
    )
    {
        $this->client = new Client($this->apiKey, $this->endpoint);
        $this->client->enableExceptions(); // throw exceptions for debug
        $this->client->disableSsl(); // disable Guzzle verification SSL
    }

    public function getByCategory(Category $category, Language $language = Language::EN_US): iterable
    {
        $query = $this->client->category()
            ->get($category, $language)
            ->setSafeSearch(SafeSearch::OFF);
        return $this->cachedSearch($query);
    }

    private function cachedSearch(Request $query)
    {
        $key = hash('xxh3', serialize($query->getQuery()));
        $news = $this->cache->get($key, function (ItemInterface $item) use ($query) {
            $item->expiresAfter($this->cacheTimeout);
            $request = $query->request();
            $newsAnswer = new NewsAnswer(...$request->getResponseData());
            return $newsAnswer;
        });
        return $news;
//        return $query->getApiClient()->request($query);
    }

    public function searchByKeyword(?string $keyword = null, $quantity=100): ?NewsAnswer
    {
//        dd(SortBy::DATE, Language::EN_US);
        $query = $this->client->search()
            ->get($keyword)
//            ->sortBy(SortBy::DATE)
            ->setQuantity($quantity);
        return $this->cachedSearch($query);
    }


}
