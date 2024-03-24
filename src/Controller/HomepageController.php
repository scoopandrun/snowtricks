<?php

namespace App\Controller;

use App\Service\TrickService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomepageController extends AbstractController
{
    #[Route(
        path: '/',
        name: 'homepage.index',
        methods: ['GET'],
    )]
    public function index(TrickService $trickService): Response
    {
        $batch = $trickService->getBatch();

        return $this->render(
            'homepage/index.html.twig',
            compact("batch")
        );
    }
}
