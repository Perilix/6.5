<?php

namespace App\Controller;

use App\Entity\CommentFeedback;
use App\Entity\CommentReport;
use App\Form\CommentReportType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Comment;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class CommentController extends AbstractController
{
    #[Route('/comment/{id}/like', name: 'comment_like', methods: ['POST'])]
    public function like(Comment $comment, EntityManagerInterface $em): RedirectResponse
    {
        $user = $this->getUser();

        if ($comment->getAuthor() === $user) {
            return $this->redirectToRoute('post_show', ['id' => $comment->getPost()->getId()]);
        }

        $existingFeedback = $em->getRepository(CommentFeedback::class)->findOneBy([
            'comment' => $comment,
            'user' => $user,
        ]);

        $this->addLike($existingFeedback, $em, $comment, $user);

        return $this->redirectToRoute('post_show', ['id' => $comment->getPost()->getId()]);
    }

    #[Route('/comment/{id}/dislike', name: 'comment_dislike', methods: ['POST'])]
    public function dislike(Comment $comment, EntityManagerInterface $em): RedirectResponse
    {
        $user = $this->getUser();



        if ($comment->getAuthor() === $user) {
            return $this->redirectToRoute('post_show', ['id' => $comment->getPost()->getId()]);
        }

        $existingFeedback = $em->getRepository(CommentFeedback::class)->findOneBy([
            'comment' => $comment,
            'user' => $user,
        ]);

        $this->addDislike($existingFeedback, $em, $comment, $user);

        return $this->redirectToRoute('post_show', ['id' => $comment->getPost()->getId()]);
    }

    #[Route('/comment/{id}/ajax/like', name: 'comment_ajax_like', methods: ['POST'])]
    public function ajaxLike(Comment $comment, EntityManagerInterface $em): \Symfony\Component\HttpFoundation\JsonResponse
    {
        $user = $this->getUser();

        if ($comment->getAuthor() === $user) {
            return $this->json(['error' => 'Vous ne pouvez pas liker votre propre commentaire.'], 400);
        }

        if (!$this->isGranted('ROLE_USER_REGISTERED')) {
            return $this->json(['error' => 'Vous devez valider votre email pour effectuer cette action.'], 403);
        }

        $existingFeedback = $em->getRepository(CommentFeedback::class)->findOneBy([
            'comment' => $comment,
            'user' => $user,
        ]);

        $this->addLike($existingFeedback, $em, $comment, $user);

        return $this->json(['likes' => $comment->countLikes(), 'dislikes' => $comment->countDislikes()]);
    }

    #[Route('/comment/{id}/ajax/dislike', name: 'comment_ajax_dislike', methods: ['POST'])]
    public function ajaxDislike(Comment $comment, EntityManagerInterface $em): \Symfony\Component\HttpFoundation\JsonResponse
    {
        $user = $this->getUser();

        if ($comment->getAuthor() === $user) {
            return $this->json(['error' => 'Vous ne pouvez pas disliker votre propre commentaire.'], 400);
        }

        if (!$this->isGranted('ROLE_USER_REGISTERED')) {
            return $this->json(['error' => 'Vous devez valider votre email pour effectuer cette action.'], 403);
        }

        $existingFeedback = $em->getRepository(CommentFeedback::class)->findOneBy([
            'comment' => $comment,
            'user' => $user,
        ]);

        $this->addDislike($existingFeedback, $em, $comment, $user);

        return $this->json(['likes' => $comment->countLikes(), 'dislikes' => $comment->countDislikes()]);
    }

    /**
     * @param CommentFeedback|null $existingFeedback
     * @param EntityManagerInterface $em
     * @param Comment $comment
     * @param UserInterface|null $user
     * @return void
     */
    private function addLike(CommentFeedback|null $existingFeedback, EntityManagerInterface $em, Comment $comment, ?UserInterface $user): void
    {
        if ($existingFeedback) {
            if ($existingFeedback->getType() === 'like') {
                $em->remove($existingFeedback);
            } else {
                $existingFeedback->setType('like');
                $em->persist($existingFeedback);
            }
        } else {
            $feedback = new CommentFeedback();
            $feedback->setComment($comment);
            $feedback->setUser($user);
            $feedback->setType('like');

            $em->persist($feedback);
        }

        $em->flush();
    }

    /**
     * @param CommentFeedback|null $existingFeedback
     * @param EntityManagerInterface $em
     * @param Comment $comment
     * @param UserInterface|null $user
     * @return void
     */
    private function addDislike(CommentFeedback|null $existingFeedback, EntityManagerInterface $em, Comment $comment, ?UserInterface $user): void
    {
        if ($existingFeedback) {
            if ($existingFeedback->getType() === 'dislike') {
                $em->remove($existingFeedback);
            } else {
                $existingFeedback->setType('dislike');
                $em->persist($existingFeedback);
            }
        } else {
            $feedback = new CommentFeedback();
            $feedback->setComment($comment);
            $feedback->setUser($user);
            $feedback->setType('dislike');

            $em->persist($feedback);
        }

        $em->flush();
    }

    #[Route('/comment/report/{id}', name: 'comment_report')]
    public function report(Comment $comment, Request $request, EntityManagerInterface $em): Response
    {
        $report = new CommentReport();
        $report->setComment($comment);
        $report->setUser($this->getUser());

        $form = $this->createForm(CommentReportType::class, $report);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($report);
            $em->flush();

            return $this->redirectToRoute('post_show', ['id' => $comment->getPost()->getId()]);
        }

        return $this->render('comment/report.html.twig', [
            'form' => $form->createView(),
            'comment' => $comment,
        ]);
    }
}
