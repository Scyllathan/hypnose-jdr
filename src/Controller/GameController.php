<?php

namespace App\Controller;

use App\Entity\Character;
use App\Entity\Game;
use App\Entity\Summary;
use App\Form\GameType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GameController extends AbstractController
{
    public function __construct(private ManagerRegistry $doctrine) {}

    #[Route('/mj/nouvelle-partie', name: 'app_new_game')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $game = new Game();
        $form = $this->createForm(GameType::class, $game);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->addFlash('success', 'Partie créée !');
            $user = $this->getUser();
            $game->setUser($user);

            $characters = [];

            $characters[] = $form->get("character1")->getData();
            $characters[] = $form->get("character2")->getData();
            $characters[] = $form->get("character3")->getData();
            $characters[] = $form->get("character4")->getData();
            $characters[] = $form->get("character5")->getData();

            $entityManager = $this->doctrine->getManager();
            $repository = $entityManager->getRepository(Character::class);

            foreach ($characters as $character) {
                if ($character !== null) {
                    $character = $repository->findBy(array('id' => $character));
                    if ($character[0]->getGame() === null) {
                        $game->addCharacter($character[0]);
                    } else {
                        $this->addFlash('alert', sprintf('%s %s n\'a pas été ajouté, il est déjà lié à une autre partie !', $character[0]->getFirstName(), $character[0]->getLastName()));
                    }
                }
            }

            $entityManager->persist($game);
            $entityManager->flush();

            return $this->redirectToRoute('index');
        }

        return $this->render('game/index.html.twig', [
            'gameForm' => $form->createView(),]);
    }

    #[Route('/mj/mes-parties', name: 'app_game_list')]
    public function showGames(): Response
    {
        $entityManager = $this->doctrine->getManager();
        $repository = $entityManager->getRepository(Game::class);

        $userId = $this->getUser()->getId();
        $games = $repository->findBy(array('user' => $userId));

        return $this->render('game/game-list.html.twig', [ 'games' => $games ]);
    }

    #[Route('/mj/supprimer-partie/{id}', name: 'app_delete_game')]
    public function deleteGame(int $id): Response
    {
        $entityManager = $this->doctrine->getManager();
        $repository = $entityManager->getRepository(Game::class);
        $game = $repository->find($id);
        $userId = $this->getUser()->getId();

        if ($game && $game->getUser()->getId() === $userId) {
            $repository->remove($game, true);
            $this->addFlash('success', sprintf('La partie "%s" a bien été supprimée !', $game->getName()));
        } else if ($game && $game->getUser()->getId() !== $userId) {
            $this->addFlash('alert', 'On ne peut pas supprimer les parties des autres !');
        } else {
            $this->addFlash('alert', 'Cette partie n\'existe pas');
        }

        return $this->redirectToRoute('app_game_list');
    }

    #[Route('/mj/modifier-partie/{id}', name: 'app_modify_game')]
    public function modifyGame(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $repository = $entityManager->getRepository(Game::class);
        $game = $repository->find($id);
        $userId = $this->getUser()->getId();
        $form = $this->createForm(GameType::class, $game);
        $form->handleRequest($request);

        $games = $repository->findBy(array('user' => $userId));

        if ($game && $game->getUser()->getId() === $userId) {
            if ($form->isSubmitted() && $form->isValid()) {
                $this->addFlash('success', 'Partie modifié !');

                foreach($game->getCharacters() as $player) {
                    $game->removeCharacter($player);
                }

                $entityManager->persist($game);
                $entityManager->flush();

                $characters = [];

                $characters[] = $form->get("character1")->getData();
                $characters[] = $form->get("character2")->getData();
                $characters[] = $form->get("character3")->getData();
                $characters[] = $form->get("character4")->getData();
                $characters[] = $form->get("character5")->getData();

                $repository = $entityManager->getRepository(Character::class);

                $count = count($characters);

                for ($i = 0 ; $i < $count ; $i++) {
                    if ($characters[$i] !== null) {
                        $characters[$i] = $repository->findBy(array('id' => $characters[$i]));
                        if ($characters[$i][0]->getGame() === null) {
                            $game->addCharacter($characters[$i][0]);
                        } else {
                            $this->addFlash('alert', sprintf('%s %s n\'a pas été ajouté, il est déjà lié à une autre partie !', $characters[$i]->getFirstName(), $characters[$i]->getLastName()));
                        }
                    }
                }

                    $entityManager->persist($game);
                    $entityManager->flush();;

                    return $this->render('game/game-list.html.twig', [ 'games' => $games ]);
                }
            } else if ($game && $game->getUser()->getId() !== $userId) {
            $this->addFlash('alert', 'On ne peut pas modifier les personnages des autres !');
            return $this->render('game/game-list.html.twig', [ 'games' => $games ]);
        } else {
            $this->addFlash('alert', 'Ce personnage n\'existe pas');
            return $this->render('game/game-list.html.twig', [ 'games' => $games ]);
        }

        return $this->render('game/modify-game.html.twig', [
            'gameForm' => $form->createView(),]);
    }

    #[Route('joueur/voir-partie/{id}', name: 'app_game')]
    public function gameDetail(int $id): Response
    {
        $entityManager = $this->doctrine->getManager();
        $repository = $entityManager->getRepository(Game::class);
        $game = $repository->find($id);
        $repository = $entityManager->getRepository(Summary::class);
        $summaries = $repository->findBy(array('game' => $id));
        $userId = $this->getUser()->getId();

        $repository = $entityManager->getRepository(Character::class);
        $characters = $repository->findBy(array('user' => $userId));
        $charactersGamesIds = [];
        foreach ($characters as $character) {
            $charactersGamesIds[] = $character->getGame()->getId();
        }

        if ($game  && ($game->getUser()->getId() === $userId || in_array($id, $charactersGamesIds))) {
            return $this->render('game/game-detail.html.twig', ['game' => $game, 'summaries' => $summaries ]);
        } else if ($game && $game->getUser()->getId() !== $userId) {
            $this->addFlash('alert', 'On ne peut pas voir les parties des autres !');
        } else {
            $this->addFlash('alert', 'Cette partie n\'existe pas');
        }
        return $this->redirectToRoute('index');
    }
}
