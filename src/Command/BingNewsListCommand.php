<?php

namespace Survos\BingNewsBundle\Command;

use Psr\EventDispatcher\EventDispatcherInterface;
use Survos\BingNewsBundle\Event\RowEvent;
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
        private EventDispatcherInterface $eventDispatcher,
    )
    {
        parent::__construct();
    }

    public function __invoke(
        IO                                                                                          $io,
        #[Option(description: 'filter by top')] bool $top = false,
        #[Option(description: 'search string')] ?string $q=null,
        #[Option(description: 'category')] ?string $category=null,
        #[Option(description: '2-letter language code')] string $locale='en',
        #[Option(description: 'max to return')] int $limit = 100,

    ): int
    {
        if ($q) {
            $news = $this->bingNewsService->searchByKeyword($q, $limit);

            $table = new Table($io);
            $table->setHeaderTitle($q);
            $headers = ['id', 'Title', 'Topic', 'Tags'];
            $table->setHeaders($headers);
            $event = $this->eventDispatcher->dispatch(new RowEvent(type: RowEvent::PRE_ITERATE));
            foreach ($news->getValue() as $bingNews) {
                $event = $this->eventDispatcher->dispatch(
                    new RowEvent($bingNews, searchTerm: $q));

                $tags = [];
                foreach ($bingNews->getAbout() as $bingNewAbout) {
                    $tags[] = $bingNewAbout['name'];
                }
                $row = [
                    $bingNews->getId(),
                    substr($bingNews->getName(), 0, 28),
                    $bingNews->getCategory(),
                    join('*', $tags),
                ];
                $table->addRow($row);
            }
            $event = $this->eventDispatcher->dispatch(new RowEvent(type: RowEvent::POST_LOAD));
            $table->render();
        }
        return self::SUCCESS;

    }




}
