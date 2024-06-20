<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\PostFeedback;
use App\Form\CommentType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\PostRepository;
use App\Entity\Post;
use App\Entity\PostLike;
use App\Form\PostType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class PostController extends AbstractController
{
    #[Route('/posts', name: 'post_index')]
    public function index(PostRepository $postRepository, Request $request): Response
    {
        $searchTerm = $request->query->get('search');
        $posts = $searchTerm ? $postRepository->findBySearchTerm($searchTerm) : $postRepository->findAll();
        return $this->render('post/index.html.twig', [
            'posts' => $posts,
        ]);
    }

    #[Route('/post/{id}', name: 'post_show')]
    public function show(Post $post, Request $request, EntityManagerInterface $em): Response
    {
        $comment = new Comment();
        $commentForm = $this->createForm(CommentType::class, $comment);
        $commentForm->handleRequest($request);

        if ($commentForm->isSubmitted() && $commentForm->isValid()) {
            $comment->setPost($post);
            $comment->setAuthor($this->getUser());
            $comment->setCreatedAt();

            $em->persist($comment);
            $em->flush();

            return $this->redirectToRoute('post_show', ['id' => $post->getId()]);
        }

        return $this->render('post/show.html.twig', [
            'post' => $post,
            'commentForm' => $commentForm->createView(),
        ]);
    }

    #[Route('/admin/post/new', name: 'admin_post_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $post->setAuthor($this->getUser());
            $em->persist($post);
            $em->flush();

            return $this->redirectToRoute('post_index');
        }

        return $this->render('admin/new_post.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/post/{id}/like', name: 'post_like', methods: ['POST'])]
    public function like(Post $post, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        // Check if the user has already given feedback on the post
        $existingFeedback = $em->getRepository(PostFeedback::class)->findOneBy([
            'post' => $post,
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
            $feedback = new PostFeedback();
            $feedback->setPost($post);
            $feedback->setUser($user);
            $feedback->setType('like');

            $em->persist($feedback);
        }

        $em->flush();

        return $this->redirectToRoute('post_show', ['id' => $post->getId()]);
    }

    #[Route('/post/{id}/dislike', name: 'post_dislike', methods: ['POST'])]
    public function dislike(Post $post, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        // Check if the user has already given feedback on the post
        $existingFeedback = $em->getRepository(PostFeedback::class)->findOneBy([
            'post' => $post,
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
            $feedback = new PostFeedback();
            $feedback->setPost($post);
            $feedback->setUser($user);
            $feedback->setType('dislike');

            $em->persist($feedback);
        }

        $em->flush();

        return $this->redirectToRoute('post_show', ['id' => $post->getId()]);
    }
}