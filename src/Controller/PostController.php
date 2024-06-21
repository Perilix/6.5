<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\PostFeedback;
use App\Form\CommentType;
use App\Form\PostType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\PostRepository;

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
        $isVerifiedUser = $this->isGranted('ROLE_USER_REGISTERED');
        $commentForm = $this->createForm(CommentType::class, $comment, [
            'is_verified_user' => $isVerifiedUser,
        ]);

        $commentForm->handleRequest($request);
        if ($commentForm->isSubmitted() && $commentForm->isValid()) {
            $comment->setPost($post);
            $comment->setAuthor($this->getUser());
            $comment->setCreatedAt(new \DateTimeImmutable());

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

        if (!$this->isGranted('ROLE_USER_REGISTERED')) {
            $this->addFlash('error', 'Vous devez valider votre email pour effectuer cette action.');
            return $this->redirectToRoute('post_show', ['id' => $post->getId()]);
        }

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

        if (!$this->isGranted('ROLE_USER_REGISTERED')) {
            $this->addFlash('error', 'Vous devez valider votre email pour effectuer cette action.');
            return $this->redirectToRoute('post_show', ['id' => $post->getId()]);
        }

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