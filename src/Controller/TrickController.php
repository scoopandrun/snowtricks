<?php

namespace App\Controller;

use App\Entity\Trick;
use App\Form\TrickType;
use App\Service\TrickService;
use App\Core\FlashClasses;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

class TrickController extends AbstractController
{
    public function __construct(private TrickService $trickService)
    {
    }

    #[Route(
        '/tricks',
        name: 'trick.archive',
        methods: ["GET"]
    )]
    public function archive(): Response
    {
        $tricks = $this->trickService->findAll();

        return $this->render(
            'trick/archive.html.twig',
            compact("tricks")
        );
    }

    #[Route(
        '/tricks/{id}-{slug}',
        name: 'trick.single',
        methods: ["GET"],
        requirements: ['id' => '\d+', 'slug' => '[a-z0-9-]+']
    )]
    public function single(int $id, string $slug): Response
    {
        $trick = $this->trickService->findById($id);

        if (!$trick) {
            throw new NotFoundHttpException("Trick not found");
        }

        if ($trick->getSlug() !== $slug) {
            return $this->redirectToRoute(
                "trick.single",
                [
                    "id" => $id,
                    "slug" => $trick->getSlug()
                ]
            );
        }

        return $this->render(
            'trick/single.html.twig',
            compact("trick")
        );
    }

    #[Route(
        '/tricks/{id}/edit',
        name: 'trick.edit',
        methods: ["GET", "POST"],
        requirements: ["id" => "\d+"]
    )]
    public function edit(
        Trick $trick,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $form = $this->createForm(TrickType::class, $trick);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $trick->setUpdatedAt(new \DateTimeImmutable());

            $entityManager->flush();

            $this->addFlash(FlashClasses::SUCCESS, "The trick has been successfully modified.");

            return $this->redirectToRoute(
                "trick.single",
                [
                    "id" => $trick->getId(),
                    "slug" => $trick->getSlug()
                ]
            );
        }

        return $this->render(
            'trick/edit.html.twig',
            compact("trick", "form")
        );
    }

    #[Route(
        '/tricks/create',
        name: 'trick.create',
        methods: ["GET", "POST"]
    )]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $trick = new Trick();

        $form = $this->createForm(TrickType::class, $trick);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $slug = $trick->makeSlug();
            $trick->setSlug($slug);

            $entityManager->persist($trick);
            $entityManager->flush();

            $this->addFlash(FlashClasses::SUCCESS, "The trick has been successfully created.");

            return $this->redirectToRoute(
                "trick.single",
                [
                    "id" => $trick->getId(),
                    "slug" => $trick->getSlug()
                ]
            );
        }

        return $this->render(
            'trick/edit.html.twig',
            compact("trick", "form")
        );
    }

    #[Route(
        '/tricks-batch-{batchNumber}',
        name: 'trick.batch',
        methods: ["GET"]
    )]
    public function tricksBatch(int $batchNumber): Response
    {
        $batch = $this->trickService->getBatch($batchNumber);

        return $this->render(
            'trick/batch.html.twig',
            compact("batch")
        );
    }
}
