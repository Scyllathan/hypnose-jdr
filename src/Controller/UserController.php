<?php

namespace App\Controller;

use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
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
        $user = $this->getUser();

        // Création du formulaire
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
                }
                $this->addFlash('alert', 'Le nouveau mot de passe et sa confirmation doivent être identiques !');
                $this->redirectToRoute('app_modify_user');
            }

            // Envoi des modifications en bdd
            $entityManager = $this->doctrine->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('succes', 'Modifications effectuées !');
            return $this->redirectToRoute('app_modify_user');
        }

        return $this->render('user/modify-user.html.twig', [
            'userForm' => $form->createView() ]);
    }

    #[Route('/joueur/supprimer-utilisateur', name: 'app_delete_user')]
    public function deleteUser(): Response
    {
        $user = $this->getUser();
        $session = new Session();
        $session->invalidate();

        $entityManager = $this->doctrine->getManager();
        $entityManager->remove($user);
        $entityManager->flush();

        return $this->redirectToRoute( 'app_logout' );
    }
}
