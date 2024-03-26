<?php

namespace App\Controller;

use App\Core\FlashClasses;
use App\Entity\Comment;
use App\Entity\Trick;
use App\Form\CommentType;
use App\Security\UserRoles;
use App\Service\CommentService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\UX\Turbo\TurboBundle;

class CommentController extends AbstractController
{
    #[Route(
        path: '/comments/{id}',
        name: 'comment.single',
        methods: ['GET'],
        requirements: ['id' => Requirement::DIGITS],
    )]
    public function single(Comment $comment): Response
    {
        $trick = $comment->getTrick();

        return $this->redirectToRoute('trick.single', [
            'id' => $trick->getId(),
            'slug' => $trick->getSlug(),
        ]);
    }

    #[Route(
        path: '/comments/{trickId}/{page}',
        name: 'comment.list',
        methods: ['GET', 'POST'],
        requirements: [
            'trickId' => Requirement::DIGITS,
            'page' => Requirement::DIGITS,
        ],
    )]
    public function comments(
        int $trickId,
        int $page,
        CommentService $commentService,
    ): Response {
        $batch = $commentService->getBatch($trickId, $page);

        return $this->render(
            'comment/_list.html.twig',
            [
                'comments' => $batch,
                'trickId' => $trickId,
            ]
        );
    }

    #[Route(
        path: '/comments/{trickId}/create',
        name: 'comment.create',
        methods: ['GET', 'POST'],
        requirements: [
            'trickId' => Requirement::DIGITS,
        ],
    )]
    public function create(
        string $trickId,
        Request $request,
        EntityManagerInterface $entityManager,
        Security $security,
    ): Response {
        $comment = (new Comment())
            ->setTrick($trick = $entityManager->getRepository(Trick::class)->find($trickId))
            ->setAuthor($security->getUser());

        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->denyAccessUnlessGranted(UserRoles::VERIFIED);

            $entityManager->persist($comment);
            $entityManager->flush();


            if ($request->getPreferredFormat() === TurboBundle::STREAM_FORMAT) {
                return $this->render('comment/_create.html.twig', [
                    'comment' => $comment,
                    'form' => $form,
                    'commentPosted' => true,
                ]);
            }

            $this->addFlash(FlashClasses::SUCCESS, "Your comment has been posted.");

            return $this->redirectToRoute('trick.single', [
                'id' => $trickId,
                'slug' => $trick->getSlug(),
            ]);
        }

        return $this->render(
            'comment/_create.html.twig',
            [
                'comment' => $comment,
                'form' => $form,
            ]
        );
    }

    #[Route(
        path: '/comments/{id}/edit',
        name: 'comment.edit',
        methods: ["GET", "POST"],
        requirements: ["id" => Requirement::DIGITS],
    )]
    #[IsGranted(UserRoles::VERIFIED)]
    public function edit(
        Comment $comment,
        Request $request,
        EntityManagerInterface $entityManager,
    ): Response {
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->flush();

            if ($request->getPreferredFormat() === TurboBundle::STREAM_FORMAT) {
                return $this->render('comment/_card.html.twig', [
                    'comment' => $comment,
                ]);
            }

            $this->addFlash(FlashClasses::SUCCESS, "Your comment has been updated.");

            return $this->redirectToRoute('trick.single', [
                'id' => $comment->getTrick()->getId(),
                'slug' => $comment->getTrick()->getSlug(),
            ]);
        }

        return $this->render(
            'comment/_edit.html.twig',
            [
                'comment' => $comment,
                'form' => $form,
            ]
        );
    }

    #[Route(
        path: "/comment/{id}",
        name: "comment.delete",
        methods: ["DELETE"],
        requirements: ["id" => Requirement::DIGITS],
    )]
    #[IsGranted(UserRoles::VERIFIED)]
    public function delete(
        Comment $comment,
        CommentService $commentService,
        EntityManagerInterface $entityManager,
        Request $request,
        Security $security,
    ): Response {
        if ($security->getUser() !== $comment->getAuthor()) {
            throw new AccessDeniedException("You are not the author of this comment. You cannot delete it.");
        }

        $commentService->remove($comment);
        $entityManager->flush();

        if ($request->getPreferredFormat() === TurboBundle::STREAM_FORMAT) {
            return $this->render('comment/_delete.html.twig', [
                'comment' => $comment,
            ]);
        }

        $this->addFlash(FlashClasses::INFO, "Your comment has been deleted.");

        return $this->redirectToRoute('trick.single', [
            'id' => $comment->getTrick()->getId(),
            'slug' => $comment->getTrick()->getSlug(),
        ]);
    }
}
