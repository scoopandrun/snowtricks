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
        '/tricks-batch-{batchNumber}',
        name: 'trick.batch',
        methods: ["GET"]
    )]
    public function batch(int $batchNumber): Response
    {
        $batch = $this->trickService->getBatch($batchNumber);

        return $this->render(
            'trick/_batch.html.twig',
            compact("batch")
        );
    }

    #[Route(
        '/tricks/{id}-{slug}',
        name: 'trick.single',
        methods: ["GET"],
        requirements: ['id' => '\d+', 'slug' => '[a-zA-Z0-9-]+']
    )]
    public function single(int $id, string $slug): Response
    {
        $trick = $this->trickService->findById($id);

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
            $picturesForms = $form->get('pictures');
            $picturesSaved = $this->trickService->savePictures($picturesForms, $trick);

            if (!$picturesSaved) {
                $this->addFlash(FlashClasses::WARNING, "The pictures have not been saved.");
            }

            $entityManager->flush();

            $this->addFlash(FlashClasses::SUCCESS, "The trick has been successfully modified.");

            return $this->redirectToRoute(
                "trick.single",
                [
                    "id" => $trick->getId(),
                    "slug" => $trick->getSlug()
                ],
                303
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
            $picturesForms = $form->get('pictures');
            $picturesSaved = $this->trickService->savePictures($picturesForms, $trick);

            if (!$picturesSaved) {
                $this->addFlash(FlashClasses::WARNING, "The pictures have not been saved.");
            }

            $entityManager->persist($trick);
            $entityManager->flush();

            $this->addFlash(FlashClasses::SUCCESS, "The trick has been successfully created.");

            return $this->redirectToRoute(
                "trick.single",
                [
                    "id" => $trick->getId(),
                    "slug" => $trick->getSlug()
                ],
                303
            );
        }

        return $this->render(
            'trick/edit.html.twig',
            compact("trick", "form")
        );
    }

    #[Route(
        "/tricks/{id}",
        name: "trick.delete",
        requirements: ["id" => "\d+"],
        methods: ["DELETE"]
    )]
    public function delete(Trick $trick, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($trick);
        $entityManager->flush();

        $this->addFlash(FlashClasses::SUCCESS, "The trick has been deleted.");

        return $this->redirectToRoute("trick.archive", status: 303);
    }
}
