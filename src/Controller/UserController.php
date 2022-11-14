<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ReinitType;
use App\Form\UserType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    public function __construct(private ManagerRegistry $doctrine) {}

    #[Route('/joueur/utilisateur', name: 'app_user')]
    public function index(): Response
    {
        return $this->render('user/index.html.twig', []);
    }

    #[Route('/joueur/modifier-utilisateur', name: 'app_modify_user')]
    public function modifyUser(Request $request, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        // Récupération de l'utilisateur et création du formulaire associé
        $user = $this->getUser();
        $form = $this->createForm(UserType::class, $this->getUser());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Si le mot de passe a été changé, hasher le nouveau puis remplacer l'ancien mdp dans l'entité
            if ($form->get('plainPassword')->getData() !== null && $form->get('confirmPlainPassword')->getData() !==
                null) {
                if ($form->get('plainPassword')->getData() === $form->get('confirmPlainPassword')->getData()) {
                    $this->getUser()->setPassword(
                        $userPasswordHasher->hashPassword(
                            $user,
                            $form->get('plainPassword')->getData()
                        )
                    );
                } else {
                    $this->addFlash('alert', 'Le nouveau mot de passe et sa confirmation doivent être identiques !');
                    return $this->redirectToRoute('app_modify_user');
                }
            }

            // Envoi des modifications en bdd
            $entityManager = $this->doctrine->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Modifications effectuées !');
            return $this->redirectToRoute('app_modify_user');
        }

        return $this->render('user/modify-user.html.twig', [
            'userForm' => $form->createView() ]);
    }

    #[Route('/joueur/supprimer-utilisateur', name: 'app_delete_user')]
    public function deleteUser(): Response
    {
        // Récupération de l'utilisateur
        $user = $this->getUser();

        // Remplacement et invalidation de la session pour rendre la suppression de l'utilisateur possible
        $session = new Session();
        $session->invalidate();

        // Suppression de l'utilisateur
        $entityManager = $this->doctrine->getManager();
        $entityManager->remove($user);
        $entityManager->flush();

        return $this->redirectToRoute( 'app_logout' );
    }

    #[Route('/mot-de-passe-oublie', name: 'app_forgotten_password')]
    public function forgottenPassword(Request $request, UserPasswordHasherInterface $userPasswordHasher, MailerInterface $mailer): Response
    {
        // Création du formulaire de réinitialisation
        $form = $this->createForm(ReinitType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupération de l'email et recherche en BDD de l'utilisateur correspondant
            $email = $form->get('email')->getData();
            $entityManager = $this->doctrine->getManager();
            $repository = $entityManager->getRepository(User::class);
            $user = $repository->findBy(array('email' => $email));

            if ($user) {
                // Si l'utilisateur existe, création d'un mot de passe aléatoire
                $plainPassword = bin2hex(random_bytes(5));
                // Cryptage du mdp et remplacement de l'ancien mdp en BDD
                $user[0]->setPassword($userPasswordHasher->hashPassword(
                    $user[0], $plainPassword));
                $entityManager->persist($user[0]);
                $entityManager->flush();

                // Envoi de l'email contenant le nouveau mot de passe non hashé
                $sentEmail = (new TemplatedEmail())
                    ->from('scyllathan@gmail.com')
                    ->to($email)
                    ->subject('Hypnose-jdr : Réinitialisation du mot de passe')
                    ->htmlTemplate('user/email-reinit.html.twig')
                    ->context([
                        'user' => $user[0],
                        'plainPassword' => $plainPassword
                    ]);
                $mailer->send($sentEmail);

                $this->addFlash('success', 'Mot de passe réinitialisé, merci de consulter votre boîte mail !');
                return $this->redirectToRoute('app_login');
            }

            $this->addFlash('alert', 'Cet utilisateur n\'existe pas !');
            return $this->redirectToRoute('app_forgotten_password');
        }

        return $this->render('user/forgotten-password.html.twig', [
            'reinitForm' => $form->createView()
        ]);
    }
}
