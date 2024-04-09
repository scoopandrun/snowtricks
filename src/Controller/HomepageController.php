<?php

namespace App\Controller;

use App\Service\TrickService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\UX\Turbo\TurboBundle;

class HomepageController extends AbstractController
{
    #[Route(
        path: '/',
        name: 'homepage.index',
        methods: ['GET'],
    )]
    public function index(): Response
    {
        return $this->render('homepage/index.html.twig');
    }

    #[Route(
        path: '/tricks-batch-{page}',
        name: 'homepage.batch',
        methods: ['GET'],
        requirements: ['batchNumber' => Requirement::POSITIVE_INT]
    )]
    public function batch(
        int $page,
        TrickService $trickService,
        Request $request,
    ): Response {
        $batchSize = 4;

        $batch = $trickService->getBatch($page, $batchSize);

        if ($request->getPreferredFormat() === TurboBundle::STREAM_FORMAT) {
            $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

            return $this->render(
                'homepage/_add_batch.stream.html.twig',
                [
                    'batch' => $batch,
                ]
            );
        }

        return $this->render(
            'homepage/_batch.html.twig',
            [
                'batch' => $batch,
            ]
        );
    }
}
