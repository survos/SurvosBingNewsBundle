<?php

namespace Survos\BingNewsBundle\Controller;

use Survos\BingNewsBundle\Form\SearchGuardianType;
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
    #[Route('/search', name: 'survos_the_guardian_search', methods: ['GET'])]
//    #[Template('@SurvosBingNews/search.html.twig')]
    public function search(
        Request $request,
        #[MapQueryParameter] ?string $q=null
    ): Response|array
    {

        $defaults  = [
            'q' => $q
        ];
        $form = $this->createForm(SearchGuardianType::class, $defaults, [
            'action' => $this->generateUrl('survos_the_guardian_search', ['q' => $q]),
            'method' => 'GET',
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $redirect =  $this->redirectToRoute('survos_the_guardian_search', ['q' => $form->getData()['q']]);
            dump($redirect->getTargetUrl());
            return $redirect;
        }
        if ($q) {
            $query = $this->bingNewsService->contentApi()
                // put the Content methods first
                ->setQuery($q)
                ->setQueryFields('headline')
                // then the ContentAPIEntity methods
                ->setShowFields('all')
                ->setOrderBy('newest')
                ->setOrderDate('published')
                ;
            $response = $this->bingNewsService->fetch($query);
        } else {
            $response = null;
        }
            return $this->render('@SurvosBingNews/search.html.twig', [
                'total' => $response?->total,
                'articles' => $response?->results,
                'searchForm' => $form->createView(),
            ]);
            // a nice search form
    }

    #[Route('/tags', name: 'survos_guardian_tags', methods: ['GET'])]
    #[Template('@SurvosBingNews/tags.html.twig')]
    public function tags(): Response|array
    {
        $tags  = $this->bingNewsService->tagsApi();
        $query = $tags->setPageSize(100);
        $results = $this->bingNewsService->fetch($query);
        return [
            'tags' => $results,
        ];
    }


}
