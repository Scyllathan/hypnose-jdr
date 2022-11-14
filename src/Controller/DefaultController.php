<?php

namespace App\Controller;

use App\Form\ContactType;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->render('default/index.html.twig');
    }

    #[Route('/contexte', name: 'contexte')]
    public function context(): Response
    {
        return $this->render('default/context.html.twig');
    }

    #[Route('/regles', name: 'regles')]
    public function rules(): Response
    {
        return $this->render('default/rules.html.twig');
    }

    #[Route('/contact', name: 'contact')]
    public function contact(MailerInterface $mailer, Request $request): Response
    {
        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = (new TemplatedEmail())
                ->from('scyllathan@gmail.com')
                ->to('scyllathan@gmail.com')
                ->subject($form->get('subject')->getData())
                ->htmlTemplate('default/email-contact.html.twig')
                ->context([
                    'message' => $form->get('content')->getData(),
                    'from' => $form->get('email')->getData()
                ]);

            $mailer->send($email);

            $this->addFlash('success', 'Message envoyé !');
        }

        return $this->render('default/contact.html.twig', [
            'contactForm' => $form->createView(),]);
    }
}
