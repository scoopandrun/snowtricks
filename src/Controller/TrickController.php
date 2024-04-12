<?php

namespace App\Controller;

use App\Utils\FlashClasses;
use App\Entity\Trick;
use App\Event\TrickCreatedEvent;
use App\Event\TrickUpdatedEvent;
use App\Form\TrickForm;
use App\Security\Voter\TrickVoter;
use App\Service\FileManager;
use App\Service\TrickService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\UX\Turbo\TurboBundle;

#[Route(path: '/tricks',   name: 'trick')]
class TrickController extends AbstractController
{
    public function __construct(
        private TrickService $trickService,
        private EventDispatcherInterface $dispatcher,
    ) {
    }

    #[Route(
        path: '/',
        name: '.archive',
        methods: ['GET'],
    )]
    public function archive(EntityManagerInterface $entityManager): Response
    {
        $tricks = $entityManager->getRepository(Trick::class)->findTrickCards();

        return $this->render(
            'trick/archive.html.twig',
            compact("tricks")
        );
    }

    #[Route(
        path: '/{id}-{slug}',
        name: '.single',
        methods: ['GET'],
        requirements: [
            'id' => Requirement::DIGITS,
            'slug' => Requirement::ASCII_SLUG,
        ],
    )]
    #[IsGranted(TrickVoter::VIEW)]
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
            [
                'trick' => $trick,
            ]
        );
    }

    #[Route(
        path: '/create',
        name: '.create',
        methods: ["GET", "POST"],
    )]
    #[IsGranted(TrickVoter::CREATE)]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
    ): Response {
        $trick = new Trick();

        $form = $this->createForm(
            TrickForm::class,
            $trick,
            [
                'attr' => [
                    'data-max-size-bytes' => FileManager::getPostMaxSize('B'),
                    'data-max-size-unit' => FileManager::getPostMaxSize('auto', true),
                ],
                'post_max_size_message' => sprintf(
                    "The total size of the pictures is too high (max %s). Please remove some pictures or choose smaller ones.",
                    FileManager::getPostMaxSize('auto', true)
                ),
            ]
        );
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
        path: '/{id}/edit',
        name: '.edit',
        methods: ["GET", "POST"],
        requirements: ["id" => Requirement::DIGITS],
    )]
    #[IsGranted(TrickVoter::EDIT, subject: 'trick')]
    public function edit(
        Trick $trick,
        Request $request,
        EntityManagerInterface $entityManager,
    ): Response {
        $form = $this->createForm(
            TrickForm::class,
            $trick,
            [
                'attr' => [
                    'data-post-max-size-bytes' => FileManager::getPostMaxSize('B'),
                    'data-post-max-size-unit' => FileManager::getPostMaxSize('auto', true),
                ],
                'post_max_size_message' => sprintf(
                    "The total size of the pictures is too high (max %s). Please remove some pictures or choose smaller ones.",
                    FileManager::getPostMaxSize('auto', true)
                ),
            ]
        );
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
            [
                'trick' => $trick,
                'form' => $form,
            ]
        );
    }


    #[Route(
        path: "/{id}",
        name: ".delete",
        methods: ["DELETE"],
        requirements: ["id" => Requirement::DIGITS],
    )]
    #[IsGranted(TrickVoter::DELETE, subject: 'trick')]
    public function delete(
        Trick $trick,
        EntityManagerInterface $entityManager,
        Request $request,
    ): Response {
        $id = $trick->getId();
        $trickName = $trick->getName();

        $entityManager->remove($trick);
        $entityManager->flush();

        $successMessage = "The trick \"{$trickName}\" has been deleted.";

        if ($request->getPreferredFormat() === TurboBundle::STREAM_FORMAT) {
            $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

            return $this->render(
                'trick/_delete.stream.html.twig',
                [
                    'id' => $id,
                    'message' => $successMessage,
                    'type' => FlashClasses::SUCCESS,
                ]
            );
        }

        $this->addFlash(FlashClasses::SUCCESS, $successMessage);

        return $this->redirectToRoute("trick.archive", status: 303);
    }
}
