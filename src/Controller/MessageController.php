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
        $messages = $repository->findBy(array('sendTo' => $this->getUser()->getId()), array('sendingDate' => 'DESC'));

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

        if ($message === null) {
            $this->addFlash('alert', 'Ce message n\'existe pas !');
            return $this->redirectToRoute('app_messages_list');
        } elseif ($message->getSendTo() !== $this->getUser()) {
            $this->addFlash('alert', 'On ne peux pas consulter les messages des autres !');
            return $this->redirectToRoute('app_messages_list');
        }

        if (!$message->isIsRead()) {
            $message->setIsRead(true);
            $entityManager->persist($message);
            $entityManager->flush();
        }

        if ($message->getReplyTo() !== null) {
            $messagesHistory = $repository->findBy(array('replyTo' => strval($message->getReplyTo()->getId())), array
            ( 'sendingDate' => 'DESC'));
            $messagesHistory[] = $message->getReplyTo();
        } else {
            $messagesHistory = [$message];
        }

        return $this->render('message/read-message.html.twig', [
            'message' => $message, 'messagesHistory' => $messagesHistory,
        ]);
    }

    #[Route('/supprimer-message/{id}', name: 'app_delete_message')]
    public function deleteMessage(int $id): Response
    {
        $entityManager = $this->doctrine->getManager();
        $repository = $entityManager->getRepository(Message::class);
        $message = $repository->find($id);

        if ($message === null) {
            $this->addFlash('alert', 'Ce message n\'existe pas !');
            return $this->redirectToRoute('app_messages_list');
        } elseif ($message->getSendTo() !== $this->getUser()) {
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

        if ($responseTo === null) {
            $this->addFlash('alert', 'On ne peut pas répondre à un message qui n\'existe pas !');
            return $this->redirectToRoute('app_messages_list');
        }

        if ($responseTo->getReplyTo() !== null) {
            $messagesHistory = $repository->findBy(array('replyTo' => strval($responseTo->getReplyTo()->getId())), array
            ( 'sendingDate' => 'DESC'));
            $messagesHistory[] = $responseTo->getReplyTo();
        } else {
            $messagesHistory = [$responseTo];
        }

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
            'responseForm' => $form->createView(), 'sender' => $responseTo->getSendBy(), 'messagesHistory' =>
                $messagesHistory, 'responseTo' => $responseTo,
        ]);
    }

    #[Route('/contacter-personnage/{id}', name: 'app_contact_message')]
    public function contactMessage(int $id, Request $request): Response
    {
        $entityManager = $this->doctrine->getManager();
        $repository = $entityManager->getRepository(User::class);
        $userToContact = $repository->find($id);

        if ($userToContact === null) {
            $this->addFlash('alert', 'Cet utilisateur n\'existe pas');
            return $this->redirectToRoute('app_messages_list');
        }

        $message = new Message();
        $form = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            $dateTime = new \DateTime();

            $message->setSendBy($user);
            $message->setSendTo($userToContact);
            $message->setSendingDate($dateTime);
            $entityManager->persist($message);
            $entityManager->flush();

            $this->addFlash('success', 'Message envoyé !');
            return $this->redirectToRoute('app_messages_list');

        }

        return $this->render('message/contact-message.html.twig', [
            'messageForm' => $form->createView(), 'user' => $userToContact,
            ]);
    }
}
