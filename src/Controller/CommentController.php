<?php

namespace App\Controller;

use App\Entity\CommentFeedback;
use App\Entity\CommentLike;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Comment;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;

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

        return $this->redirectToRoute('post_show', ['id' => $comment->getPost()->getId()]);
    }
}
