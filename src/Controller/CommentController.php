<?php

namespace App\Controller;

use App\Utils\FlashClasses;
use App\Entity\Comment;
use App\Entity\Trick;
use App\Form\CommentType;
use App\Security\Voter\CommentVoter;
use App\Service\CommentService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
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
    public function redirectToTrickPage(Comment $comment): Response
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
    #[IsGranted(CommentVoter::VIEW)]
    public function comments(
        int $trickId,
        int $page,
        CommentService $commentService,
    ): Response {
        $commentsPerPage = 10;
        $includeDeleted = true;

        $batch = $commentService->getBatch($trickId, $page, $commentsPerPage, $includeDeleted);

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
            $this->denyAccessUnlessGranted(CommentVoter::CREATE);

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
        path: '/comments/reply-to/{id}',
        name: 'comment.reply-to',
        methods: ['GET', 'POST'],
        requirements: [
            'trickId' => Requirement::DIGITS,
        ],
    )]
    public function replyTo(
        Comment $originalComment,
        Request $request,
        EntityManagerInterface $entityManager,
        Security $security,
    ): Response {
        $reply = (new Comment())
            ->setTrick($originalComment->getTrick())
            ->setAuthor($security->getUser())
            ->setReplyTo($originalComment);

        $form = $this->createForm(CommentType::class, $reply);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->denyAccessUnlessGranted(CommentVoter::CREATE);

            $entityManager->persist($reply);
            $entityManager->flush();


            if ($request->getPreferredFormat() === TurboBundle::STREAM_FORMAT) {
                return $this->render('comment/_reply_to.html.twig', [
                    'originalComment' => $originalComment,
                    'reply' => $reply,
                    'form' => $form,
                ]);
            }

            $this->addFlash(FlashClasses::SUCCESS, "Your comment has been posted.");

            return $this->redirectToRoute('trick.single', [
                'id' => $originalComment->getTrick()->getId(),
                'slug' => $originalComment->getTrick()->getSlug(),
            ]);
        }

        return $this->render(
            'comment/_reply_to.html.twig',
            [
                'originalComment' => $originalComment,
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
    #[IsGranted(CommentVoter::EDIT, subject: 'comment')]
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
    #[IsGranted(CommentVoter::DELETE, subject: 'comment')]
    public function delete(
        Comment $comment,
        CommentService $commentService,
        EntityManagerInterface $entityManager,
        Request $request,
    ): Response {
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
