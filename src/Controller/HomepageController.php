<?php

namespace App\Controller;

use App\Service\TrickService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomepageController extends AbstractController
{
    #[Route(
        '/',
        name: 'homepage',
        methods: ["GET"]
    )]
    public function index(TrickService $trickService): Response
    {
        $batch = $trickService->getBatch();

        return $this->render(
            'homepage/index.html.twig',
            compact("batch")
        );
    }

    #[Route(
        '/homepage-tricks-batch-{batchNumber}',
        name: 'homepage-tricks-batch',
        methods: ["GET"]
    )]
    public function tricksBatch(TrickService $trickService, int $batchNumber): Response
    {
        $batch = $trickService->getBatch($batchNumber);

        return $this->render(
            'homepage/tricks.html.twig',
            compact("batch")
        );
    }
}
