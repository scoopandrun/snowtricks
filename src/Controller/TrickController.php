<?php

namespace App\Controller;

use App\Service\TrickService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TrickController extends AbstractController
{
    #[Route(
        '/tricks',
        name: 'trick_archive',
        methods: ["GET"]
    )]
    public function archive(TrickService $trickService): Response
    {
        $tricks = $trickService->findAll();

        return $this->render(
            'trick/archive.html.twig',
            compact("tricks")
        );
    }

    #[Route(
        '/trick/{slug}',
        name: 'trick_single',
        methods: ["GET"]
    )]
    public function single(TrickService $trickService, string $slug): Response
    {
        $trick = $trickService->findOne();

        return $this->render('trick/single.html.twig', [
            'trick' => 'TrickController',
        ]);
    }

    #[Route(
        '/tricks-batch-{batchNumber}',
        name: 'tricks_batch',
        methods: ["GET"]
    )]
    public function tricksBatch(TrickService $trickService, int $batchNumber): Response
    {
        $batch = $trickService->getBatch($batchNumber);

        return $this->render(
            'trick/batch.html.twig',
            compact("batch")
        );
    }
}
