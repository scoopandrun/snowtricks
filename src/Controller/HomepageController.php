<?php

namespace App\Controller;

use App\Service\TrickService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomepageController extends AbstractController
{
    #[Route('/', name: 'homepage')]
    public function index(TrickService $trickService): Response
    {
        $tricks = $trickService->findAll();

        return $this->render(
            'homepage/index.html.twig',
            compact("tricks")
        );
    }
}
