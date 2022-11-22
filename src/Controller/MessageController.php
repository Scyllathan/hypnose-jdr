<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\User;
use App\Form\MessageType;
use App\Form\ResponseMessageType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/joueur')]
class MessageController extends AbstractController
{
    public function __construct(private ManagerRegistry $doctrine) {}

    #[Route('/liste-messages', name: 'app_messages_list')]
    public function messagesList(): Response
    {
        $entityManager = $this->doctrine->getManager();
        $repository = $entityManager->getRepository(Message::class);
        $messages = $repository->findBy(array('sendTo' => $this->getUser()->getId()));

        return $this->render('message/messages-list.html.twig', [
            'messages' => $messages
        ]);
    }

    #[Route('/nouveau-message', name: 'app_new_message')]
    public function newMessage(Request $request, EntityManagerInterface $entityManager): Response
    {
        $message = new Message();
        $form = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            $dateTime = new \DateTime();
            $idReceiver = $form->get("receiver")->getData();
            $entityManager = $this->doctrine->getManager();
            $repository = $entityManager->getRepository(User::class);
            $receiver = $repository->find($idReceiver);

            if ($receiver !== null) {
                $message->setSendBy($user);
                $message->setSendTo($receiver);
                $message->setSendingDate($dateTime);
                $entityManager->persist($message);
                $entityManager->flush();

                $this->addFlash('success', 'Message envoyé !');
                return $this->redirectToRoute('app_messages_list');
            } else {
                $this->addFlash('alert', sprintf('Aucun utilisateur ne possède l\'identifiant $d', $idReceiver));
                return $this->redirectToRoute('app_new_message');
            }

        }

        return $this->render('message/new-message.html.twig', [
                'messageForm' => $form->createView(),]
        );
    }

    #[Route('/lire-message/{id}', name: 'app_read_message')]
    public function readMessage(int $id): Response
    {
        $entityManager = $this->doctrine->getManager();
        $repository = $entityManager->getRepository(Message::class);
        $message = $repository->find($id);

        if ($message->getSendTo() !== $this->getUser()) {
            $this->addFlash('alert', 'On ne peux pas consulter les messages des autres !');
            return $this->redirectToRoute('app_messages_list');
        }

        if (!$message->isIsRead()) {
            $message->setIsRead(true);
            $entityManager->persist($message);
            $entityManager->flush();
        }

        return $this->render('message/read-message.html.twig', [
            'message' => $message
        ]);
    }

    #[Route('/supprimer-message/{id}', name: 'app_delete_message')]
    public function deleteMessage(int $id): Response
    {
        $entityManager = $this->doctrine->getManager();
        $repository = $entityManager->getRepository(Message::class);
        $message = $repository->find($id);

        if ($message->getSendTo() !== $this->getUser()) {
            $this->addFlash('alert', 'On ne peux pas supprimer les messages des autres !');
        } else {
            $repository->remove($message, true);
            $this->addFlash('success', 'Message supprimé !');
        }

        return $this->redirectToRoute('app_messages_list');
    }

    #[Route('/repondre/{id}', name: 'app_response_message')]
    public function responseMessage(int $id, Request $request): Response
    {
        $entityManager = $this->doctrine->getManager();
        $repository = $entityManager->getRepository(Message::class);
        $responseTo = $repository->find($id);

        $message = new Message();
        $form = $this->createForm(ResponseMessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($this->getUser() === $responseTo->getSendTo()) {
                $dateTime = new \DateTime();
                $message->setSendBy($this->getUser());
                $message->setSendTo($responseTo->getSendBy());
                $message->setSendingDate($dateTime);
                if ($responseTo->getReplyTo() !== null) {
                    $message->setTitle(sprintf('Re: %s', $responseTo->getReplyTo()->getTitle()));
                    $message->setReplyTo($responseTo->getReplyTo());
                } else {
                    $message->setTitle(sprintf('Re: %s', $responseTo->getTitle()));
                    $message->setReplyTo($responseTo);
                }

                $entityManager->persist($message);
                $entityManager->flush();

                $this->addFlash('success', 'Message envoyé !');
                return $this->redirectToRoute('app_messages_list');
            } else {
                $this->addFlash('alert', 'On ne peut pas répondre aux messages des autres !');
                return $this->redirectToRoute('app_messages_list');
            }
        }

        return $this->render('message/response-message.html.twig', [
            'responseForm' => $form->createView(), 'sender' => $responseTo->getSendBy()]);
    }
}
