<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    public function __construct(private EmailVerifier $emailVerifier)
    {
    }

    #[Route('/register', name: 'register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();

            // generate a signed url and email it to the user
            $email = (new TemplatedEmail())
                ->from(new Address('mailer@biture-numerique.fr', 'WebNews Mail'))
                ->to($user->getEmail())
                ->subject('WebNews - Confirmez votre email')
                ->htmlTemplate('registration/confirmation_email.html.twig');

            $this->emailVerifier->sendEmailConfirmation('verify_email', $user, $email);

            // do anything else you need here, like send an email

            return $this->redirectToRoute('profile');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #[Route('/verify/email', name: 'verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator, UserRepository $userRepository): Response
    {
        $id = $request->query->get('id');

        if (null === $id) {
            return $this->redirectToRoute('register');
        }

        $user = $userRepository->find($id);

        if (null === $user) {
            return $this->redirectToRoute('register');
        }

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('register');
        }

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $this->addFlash('success', 'Your email address has been verified.');

        return $this->redirectToRoute('login');
    }

    #[Route('/resend-verification-email', name: 'resend_verification_email', methods: ['POST'])]
    public function resendVerificationEmail(UserRepository $userRepository, TranslatorInterface $translator): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user || $user->isVerified()) {
            return $this->redirectToRoute('profile');
        }

        $email = (new TemplatedEmail())
            ->from(new Address('mailer@biture-numerique.fr', 'WebNews Mail'))
            ->to($user->getEmail())
            ->subject('WebNews - Confirmez votre email')
            ->htmlTemplate('registration/confirmation_email.html.twig');

        try {
            $this->emailVerifier->sendEmailConfirmation('verify_email', $user, $email);
        } catch (\Exception $e) {
            $this->addFlash('error', $translator->trans('email_verification_failed', [], 'messages'));
            return $this->redirectToRoute('profile');
        }

        $this->addFlash('success', 'Un nouvel email de vérification a été envoyé.');

        return $this->redirectToRoute('profile');
    }

}
