<?php

namespace App\Controller;

use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(PostRepository $postRepository): Response
    {
        $latestPosts = $postRepository->findBy([], ['createdAt' => 'DESC'], 3);

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'posts' => $latestPosts,
        ]);
    }
}
