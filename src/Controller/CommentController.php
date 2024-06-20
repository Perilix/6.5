<?php

namespace App\Controller;

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
    #[Route('/comment/add/{postId}', name: 'comment_add')]
    public function add(Request $request, $postId, PostRepository $postRepository, EntityManagerInterface $em): RedirectResponse
    {
        $post = $postRepository->find($postId);
        if (!$post) {
            throw $this->createNotFoundException('Post not found');
        }

        $comment = new Comment();
        $comment->setContent($request->request->get('content'));
        $comment->setPost($post);
        $comment->setAuthor($this->getUser());
        $comment->setCreatedAt(new \DateTimeImmutable());

        $em->persist($comment);
        $em->flush();

        return $this->redirectToRoute('post_show', ['id' => $postId]);
    }

    #[Route('/comment/{id}/like', name: 'comment_like', methods: ['POST'])]
    public function like($id, EntityManagerInterface $em): RedirectResponse
    {
        $comment = $em->getRepository(Comment::class)->find($id);
        if (!$comment) {
            throw $this->createNotFoundException('Comment not found');
        }

        $like = new CommentLike();
        $like->setComment($comment);
        $like->setUser($this->getUser());

        $em->persist($like);
        $em->flush();

        return $this->redirectToRoute('post_show', ['id' => $comment->getPost()->getId()]);
    }
}
