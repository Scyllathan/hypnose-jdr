<?php

namespace App\Controller;

use App\Entity\Character;
use App\Form\CharacterType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CharacterController extends AbstractController
{
    public function __construct(private ManagerRegistry $doctrine) {}

    #[Route('/nouveau-personnage', name: 'app_character_new')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $character = new Character();
        $form = $this->createForm(CharacterType::class, $character);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $totalCharacs = $character->getStamina() + $character->getStrength() + $character->getAgility() +
                $character->getSpeed() + $character->getIntelligence() + $character->getResilience() +
                $character->getCharisma() + $character->getLuck();

            if ($totalCharacs !== 80) {
                $this->addFlash('alert', 'La somme de vos caractéristiques doit être égale à 80 !');
                return $this->render('character/index.html.twig', [
                    'characterForm' => $form->createView(),]);
            }

            $this->addFlash('success', 'Personnage créé !');
            $user = $this->getUser();
            $character->setUser($user);
            $entityManager->persist($character);
            $entityManager->flush();

            return $this->redirectToRoute('index');
        }

        return $this->render('character/index.html.twig', [
            'characterForm' => $form->createView(),]);
    }

    #[Route('/mes-personnages', name: 'app_character_list')]
    public function characterList(): Response
    {
        $entityManager = $this->doctrine->getManager();
        $repository = $entityManager->getRepository(Character::class);

        $userId = $this->getUser()->getId();
        $characters = $repository->findBy(array('user' => $userId));
        return $this->render('character/character-list.html.twig', ['characters' => $characters]);
    }

    #[Route('/voir-personnage/{id}', name: 'app_character')]
    public function characterDetail(int $id): Response
    {
        $entityManager = $this->doctrine->getManager();
        $repository = $entityManager->getRepository(Character::class);
        $character = $repository->find($id);
        $userId = $this->getUser()->getId();

        if ($character  && $character->getUser()->getId() === $userId) {
            return $this->render('character/character-detail.html.twig', ['character' => $character]);
        } else if ($character && $character->getUser()->getId() !== $userId) {
            $this->addFlash('alert', 'On ne peut pas voir les personnages des autres !');
        } else {
            $this->addFlash('alert', 'Ce personnage n\'existe pas');
        }

        $userId = $this->getUser()->getId();
        $characters = $repository->findBy(array('user' => $userId));

        return $this->render('character/character-list.html.twig', ['characters' => $characters]);
    }

    #[Route('/supprimer-personnage/{id}', name: 'app_del_character')]
    public function deleteCharacter(int $id): Response
    {
        $entityManager = $this->doctrine->getManager();
        $repository = $entityManager->getRepository(Character::class);
        $character = $repository->find($id);
        $userId = $this->getUser()->getId();

        if ($character && $character->getUser()->getId() === $userId) {
            $repository->remove($character, true);
            $this->addFlash('success', sprintf('%s %s a bien été supprimé !', $character->getFirstName(),
                $character->getLastName()));
        } else if ($character && $character->getUser()->getId() !== $userId) {
            $this->addFlash('alert', 'On ne peut pas supprimer les personnages des autres !');
        } else {
            $this->addFlash('alert', 'Ce personnage n\'existe pas');
        }

        $characters = $repository->findBy(array('user' => $userId));

        return $this->render('character/character-list.html.twig', ['characters' => $characters]);
    }

    #[Route('/modifier-personnage/{id}', name:'app_modify_character')]
    public function modifyCharacter(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $entityManager = $this->doctrine->getManager();
        $repository = $entityManager->getRepository(Character::class);
        $character = $repository->find($id);
        $userId = $this->getUser()->getId();
        $characters = $repository->findBy(array('user' => $userId));
        $form = $this->createForm(CharacterType::class, $character);
        $form->handleRequest($request);

        if ($character && $character->getUser()->getId() === $userId) {
            if ($form->isSubmitted() && $form->isValid()) {
                $this->addFlash('success', 'Personnage modifié !');

                $character = $form->getData();
                $entityManager->persist($character);
                $entityManager->flush();

                return $this->render('character/character-list.html.twig', ['characters' => $characters]);
            }
        } else if ($character && $character->getUser()->getId() !== $userId) {
            $this->addFlash('alert', 'On ne peut pas modifier les personnages des autres !');
            return $this->render('character/character-list.html.twig', ['characters' => $characters]);
        } else {
            $this->addFlash('alert', 'Ce personnage n\'existe pas');
            return $this->render('character/character-list.html.twig', ['characters' => $characters]);
        }

        return $this->render('character/modify-character.html.twig', [
            'characterForm' => $form->createView(), 'character' => $character]);
    }
}
