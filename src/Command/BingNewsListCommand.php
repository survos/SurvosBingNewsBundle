<?php

namespace Survos\BingNewsBundle\Command;

use Survos\BingNewsBundle\Service\BingNewsService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Helper\Table;
use Zenstruck\Console\Attribute\Argument;
use Zenstruck\Console\Attribute\Option;
use Zenstruck\Console\InvokableServiceCommand;
use Zenstruck\Console\IO;
use Zenstruck\Console\RunsCommands;
use Zenstruck\Console\RunsProcesses;

#[AsCommand('bing-news:list', 'list bing-news sources and articles (various endpoints)')]
final class BingNewsListCommand extends InvokableServiceCommand
{
    use RunsCommands;
    use RunsProcesses;

    public function __construct(
        private readonly BingNewsService $bingNewsService,
    )
    {
        parent::__construct();
    }

    public function __invoke(
        IO                                                                                          $io,
        #[Argument(description: 'endpoint (source, search)')] string        $endpoint='',
        #[Option(description: 'filter by top')] bool $top = false,
        #[Option(description: 'search string')] ?string $q=null,
        #[Option(description: '2-letter language code')] string $locale='en',

    ): int
    {
        if ($q) {
            $query = $this->bingNewsService->contentApi()
                ->setQuery($q);
            $response = $this->bingNewsService->fetch($query);

            $table = new Table($io);
            $table->setHeaderTitle($q);
            $headers = ['Title', 'Url'];
            $table->setHeaders($headers);
            foreach ($response->results as $rowData) {
                $row = [
                    $rowData->webTitle,
                    $rowData->webUrl,
                ];
                $table->addRow($row);
            }
            $table->render();
        }
        return self::SUCCESS;

    }




}
