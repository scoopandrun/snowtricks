<?php

namespace App\Controller;

use App\Core\FlashClasses;
use App\Entity\Trick;
use App\Event\TrickCreatedEvent;
use App\Event\TrickUpdatedEvent;
use App\Form\TrickType;
use App\Service\TrickService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class TrickController extends AbstractController
{
    public function __construct(
        private TrickService $trickService,
        private EventDispatcherInterface $dispatcher,
    ) {
    }

    #[Route(
        '/tricks',
        name: 'trick.archive',
        methods: ["GET"]
    )]
    public function archive(EntityManagerInterface $entityManager): Response
    {
        $tricks = $entityManager->getRepository(Trick::class)->findAll();

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
    public function single(Trick $trick, string $slug): Response
    {
        if ($trick->getSlug() !== $slug) {
            return $this->redirectToRoute(
                "trick.single",
                [
                    "id" => $trick->getId(),
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
    #[IsGranted('ROLE_USER')]
    public function edit(
        Trick $trick,
        Request $request,
        EntityManagerInterface $entityManager,
    ): Response {
        $form = $this->createForm(TrickType::class, $trick);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->dispatcher->dispatch(new TrickUpdatedEvent($trick));

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
    #[IsGranted('ROLE_USER')]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
    ): Response {
        $trick = new Trick();

        $form = $this->createForm(TrickType::class, $trick);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->dispatcher->dispatch(new TrickCreatedEvent($trick));

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
    #[IsGranted('ROLE_USER')]
    public function delete(
        Trick $trick,
        EntityManagerInterface $entityManager,
    ): Response {
        $entityManager->remove($trick);
        $entityManager->flush();

        $this->addFlash(FlashClasses::SUCCESS, "The trick has been deleted.");

        return $this->redirectToRoute("trick.archive", status: 303);
    }
}
