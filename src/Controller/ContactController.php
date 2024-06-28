<?php

namespace App\Controller;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use App\Form\ContactType;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'contact')]
    public function index(Request $request, MailerInterface $mailer): Response
    {
        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $email = (new Email())
                ->from($data['email'])
                ->to('contact@biture-numerique.fr')
                ->subject('Contact Form Submission')
                ->text($data['message']);

            $mailer->send($email);

            // Email to user
            $userConfirmationEmail = (new TemplatedEmail())
                ->from(new Address('mailer@biture-numerique.fr', 'WebNews Mail'))
                ->to($data['email'])
                ->subject('Confirmation de réception de votre message')
                ->htmlTemplate('contact/confirmation_email.html.twig')
                ->context([
                    'name' => $data['name'],
                    'message' => $data['message'],
                ]);

            $mailer->send($userConfirmationEmail);

            $this->addFlash('success', 'Votre message a bien été envoyé !');

            return $this->redirectToRoute('contact');
        }

        return $this->render('contact/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}