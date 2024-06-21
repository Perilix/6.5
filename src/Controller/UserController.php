<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserProfileFormType;
use App\Repository\PostRepository;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController
{
    private EmailVerifier $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
    }

    #[Route('/profile', name: 'profile')]
    public function index(Request $request, PostRepository $postRepository, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher, TokenStorageInterface $tokenStorage): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw new \LogicException('The user is not an instance of App\Entity\User.');
        }

        $form = $this->createForm(UserProfileFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newEmail = $form->get('email')->getData();
            $originalEmail = $user->getEmail();

            if ($form->get('plainPassword')->getData()) {
                $user->setPassword(
                    $passwordHasher->hashPassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );
            }

            if ($newEmail !== $originalEmail) {
                $user->setEmail($newEmail);
                $user->setVerified(false);

                $email = (new TemplatedEmail())
                    ->from(new Address('mailer@biture-numerique.fr', 'WebNews Mail'))
                    ->to($user->getEmail())
                    ->subject('WebNews - Confirmez votre email')
                    ->htmlTemplate('registration/confirmation_email.html.twig');

                $this->emailVerifier->sendEmailConfirmation('verify_email', $user, $email);
            }

            $em->persist($user);
            $em->flush();

            // Update the token if username is changed
            $token = $tokenStorage->getToken();
            if ($token) {
                $token->setUser($user);
            }

            $this->addFlash('success', 'Profil mis à jour avec succès.');

            return $this->redirectToRoute('profile');
        }

        $likedPosts = $postRepository->findLikedPostsByUser($user);

        return $this->render('user/profile.html.twig', [
            'user' => $user,
            'likedPosts' => $likedPosts,
            'profileForm' => $form->createView(),
        ]);
    }

    #[Route('/profile/delete', name: 'delete_account', methods: ['DELETE'])]
    public function deleteAccount(User $user, EntityManagerInterface $em, TokenStorageInterface $tokenStorage): Response
    {
        $em->remove($user);
        $em->flush();

        $tokenStorage->setToken(null);
        $this->container->get('session')->invalidate();

        $this->addFlash('success', 'Votre compte a été supprimé.');

        return $this->redirectToRoute('homepage');
    }
}