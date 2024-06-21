<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\User;
use App\Form\CategoryType;
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

    #[Route('/admin/post/new', name: 'admin_post_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
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
