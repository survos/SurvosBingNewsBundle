# BingNewsBundle

A Symfony bundle to interact with Bing News API  via the [bing-news-API library](https://github.com/tacman/BingNewsSearch).

Still under development, feedback welcome!  

## Quickstart
```bash
symfony new bing-news-demo --webapp && cd bing-news-demo
composer require survos/bing-news-bundle
```

composer config repositories.survos_bing_news_bundle '{"type": "path", "url": "../survos/packages/bing-news-bundle"}'
composer req survos/bing-news-bundle:"*@dev"

## Resources

* https://portal.azure.com/#@tacmangmail.onmicrosoft.com/resource/subscriptions/2809ba0c-d042-49a8-a2f5-4363572b8c8a/resourceGroups/News/providers/Microsoft.Bing/accounts/BingNews/overview
* 

## Installation

Go to Microsoft Azure and get a key.


Create a new Symfony project.

```bash
symfony new bing-news-demo --webapp && cd bing-news-demo
composer require survos/bing-news-bundle
bin/console bing-news:list
```

You can browse interactively with the basic admin controller.

```bash
composer require survos/simple-datatables-bundle
symfony server:start -d
symfony open:local --path=/bing-news/
```

Or edit .env.local and add your API key.


```bash
bin/console bing-news:list 
```

```bash
+------------- museado/ -----+--------+
| ObjectName     | Path      | Length |
+----------------+-----------+--------+
| photos finales | /museado/ | 0      |
+----------------+-----------+--------+


```

