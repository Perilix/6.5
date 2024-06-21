<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\CommentReport;
use App\Entity\User;
use App\Form\CategoryType;
use App\Repository\CommentReportRepository;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Post;
use App\Form\PostType;
use Doctrine\ORM\EntityManagerInterface;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'admin_dashboard')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig');
    }

    #[Route('/admin/users', name: 'admin_users')]
    public function users(EntityManagerInterface $em): Response
    {
        $users = $em->getRepository(User::class)->findAll();
        return $this->render('admin/users.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/admin/user/{id}/ban', name: 'admin_user_ban', methods: ['POST'])]
    public function banUser(User $user, EntityManagerInterface $em): Response
    {
        $user->setRoles([]);
        $user->setBanned(true);
        $em->persist($user);
        $em->flush();

        return $this->redirectToRoute('admin_users');
    }

    #[Route('/admin/user/{id}/unban', name: 'admin_user_unban', methods: ['POST'])]
    public function unbanUser(User $user, EntityManagerInterface $em): Response
    {
        $user->setBanned(false);
        $em->persist($user);
        $em->flush();

        return $this->redirectToRoute('admin_users');
    }

    #[Route('/admin/user/{id}/roles', name: 'admin_user_roles', methods: ['POST'])]
    public function manageRoles(Request $request, User $user, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $roles = $request->request->all('roles');

            $user->setRoles($roles);
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('admin_users');
        }

        return $this->render('admin/users.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/admin/posts', name: 'admin_posts')]
    public function listPosts(Request $request, PostRepository $postRepository): Response
    {
        $criteria = $request->query->get('criteria', 'date');
        $order = $request->query->get('order', 'desc');
        $search = $request->query->get('search', '');

        $posts = $postRepository->findByCriteria($criteria, $order, $search);

        return $this->render('admin/posts.html.twig', [
            'posts' => $posts,
        ]);
    }

    #[Route('/admin/post/new', name: 'admin_post_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {

        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $post = new Post();
        $form = $this->createForm(PostType::class, $post);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($post);
            $em->flush();

            return $this->redirectToRoute('admin_dashboard');
        }

        return $this->render('admin/new_post.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/post/edit/{id}', name: 'admin_post_edit')]
    public function editPost(Post $post, Request $request, EntityManagerInterface $em): Response
    {

        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            return $this->redirectToRoute('admin_posts');
        }

        return $this->render('admin/edit_post.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/post/delete/{id}', name: 'admin_post_delete', methods: ['POST','DELETE'])]
    public function deletePost(Post $post, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $em->remove($post);
        $em->flush();

        return $this->redirectToRoute('admin_posts');
    }

    #[Route('/admin/reports', name: 'admin_reports')]
    public function listReports(CommentReportRepository $commentReportRepository): Response
    {
        $reports = $commentReportRepository->findAll();

        return $this->render('admin/reports.html.twig', [
            'reports' => $reports,
        ]);
    }

    #[Route('/admin/report/delete/{id}', name: 'admin_report_delete', methods: ['POST'])]
    public function deleteReport(CommentReport $report, EntityManagerInterface $em): Response
    {
        $em->remove($report);
        $em->flush();

        return $this->redirectToRoute('admin_reports');
    }

    #[Route('/admin/comment/delete/{id}', name: 'admin_comment_delete', methods: ['POST', 'DELETE'])]
    public function deleteComment(Comment $comment, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $em->remove($comment);
        $em->flush();

        $origin = $request->query->get('origin');

        if ($origin === 'admin_reports') {
            return $this->redirectToRoute('admin_reports');
        }

        return $this->redirectToRoute('post_show', ['id' => $comment->getPost()->getId()]);
    }

    #[Route('/admin/category/new', name: 'admin_category_new')]
    public function newCategory(Request $request, EntityManagerInterface $em): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($category);
            $em->flush();

            return $this->redirectToRoute('admin_post_new');
        }

        return $this->render('admin/new_category.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
