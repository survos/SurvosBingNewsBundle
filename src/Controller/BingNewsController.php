<?php

namespace Survos\BingNewsBundle\Controller;

use Survos\BingNewsBundle\Form\SearchFormType;
use Survos\BingNewsBundle\Service\BingNewsService;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

class BingNewsController extends AbstractController
{
    public function __construct(
        private BingNewsService $bingNewsService,
        private $simpleDatatablesInstalled = false
    )
    {
        $this->checkSimpleDatatablesInstalled();
    }

    private function checkSimpleDatatablesInstalled()
    {
        if (! $this->simpleDatatablesInstalled) {
            throw new \LogicException("This page requires SimpleDatatables\n composer req survos/simple-datatables-bundle");
        }
    }
    #[Route('/search', name: 'survos_bing_news_search', methods: ['GET'])]
//    #[Template('@SurvosBingNews/search.html.twig')]
    public function search(
        Request $request,
        #[MapQueryParameter] ?string $q=null
    ): Response|array
    {

        $defaults  = [
            'q' => $q
        ];
        $form = $this->createForm(SearchFormType::class, $defaults, [
            'action' => $this->generateUrl('survos_bing_news_search', ['q' => $q]),
            'method' => 'GET',
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $redirect =  $this->redirectToRoute('survos_bing_news_search', ['q' => $form->getData()['q']]);
            return $redirect;
        }
        $news = $q ? $this->bingNewsService->searchByKeyword($q) : [];
            return $this->render('@SurvosBingNews/search.html.twig', [
                'news' => $news,
                'searchForm' => $form->createView(),
            ]);
            // a nice search form
    }
}
