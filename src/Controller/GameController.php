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
    public function newGame(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Création d'une nouvelle partie et du formulaire vide associé
        $game = new Game();
        $form = $this->createForm(GameType::class, $game);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Définition de l'utilisateur courant comme créateur de la partie
            $user = $this->getUser();
            $game->setUser($user);

            // Récupération dans un tableau des entrées de personnage dans le formulaire
            $characters = [];
            $characters[] = $form->get("character1")->getData();
            $characters[] = $form->get("character2")->getData();
            $characters[] = $form->get("character3")->getData();
            $characters[] = $form->get("character4")->getData();
            $characters[] = $form->get("character5")->getData();

            // Pour chaque personnage entré dans le formulaire, on récupère l'entité character si elle existe.
            foreach ($characters as $character) {
                if ($character !== null) {
                    $repository = $entityManager->getRepository(Character::class);
                    $character = $repository->find($character);
                    $characterGame = null;
                    // Si le personnage n'a pas encore de partie, on le lie à la partie
                    if ($character) {
                        $characterGame = $character->getGame();
                        if ($characterGame === null) {
                            $game->addCharacter($character);
                        } else {
                            $this->addFlash('alert', sprintf('%s %s n\'a pas été ajouté, il est déjà lié à une autre partie !', $character->getFirstName(), $character->getLastName()));
                        }
                    } else {
                        $this->addFlash('alert', 'Vous avez tenté d\'ajouter un personnage qui n\'existe pas');
                    }
                }
            }

            // Envoie en BDD du tuple
            $entityManager->persist($game);
            $entityManager->flush();

            $this->addFlash('success', 'Partie créée !');
            return $this->redirectToRoute('app_game_list');
        }

        return $this->render('game/index.html.twig', [
            'gameForm' => $form->createView(),]);
    }

    #[Route('/mj/mes-parties', name: 'app_game_list')]
    public function showGames(): Response
    {
        // Récupération en BDD de toutes les parties de l'utilisateur
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

        // Vérification que la partie existe et que l'utilisateur en est le créateur
        if ($game && $game->getUser()->getId() === $userId) {
            // Récupération en BDD des personnages liés à la partie
            $repository2 = $entityManager->getRepository(Character::class);
            $characters = $repository2->findBy( array('game' => $game->getId()));
            // Suppression du contenu de la colonne game pour chaque personnage de la partie et envoi en BDD
            if ($characters) {
                foreach ($characters as $character) {
                    $character->setGame(null);
                    $entityManager->persist($character);
                    $entityManager->flush();
                }
            }
            // Suppression de la partie et envoi en BDD
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
        // Récupération en BDD de la partie correspondant à l'id de l'url
        $repository = $entityManager->getRepository(Game::class);
        $game = $repository->find($id);

        // Création du formulaire prérempli avec l'entité game récupérée
        $form = $this->createForm(GameType::class, $game);
        $form->handleRequest($request);

        $userId = $this->getUser()->getId();

        // Vérification que la partie existe et que l'utilisateur en est le créateur
        if ($game && $game->getUser()->getId() === $userId) {
            if ($form->isSubmitted() && $form->isValid()) {

                // Suppression du contenu de la colonne game pour chaque ancien personnage de la partie et envoi en BDD
                foreach($game->getCharacters() as $player) {
                    $game->removeCharacter($player);
                }
                $entityManager->persist($game);
                $entityManager->flush();

                // Récupération dans un tableau des nouvelles entrées de personnage dans le formulaire
                $characters = [];
                $characters[] = $form->get("character1")->getData();
                $characters[] = $form->get("character2")->getData();
                $characters[] = $form->get("character3")->getData();
                $characters[] = $form->get("character4")->getData();
                $characters[] = $form->get("character5")->getData();

                // Pour chaque personnage entré dans le formulaire, on récupère l'entité character si elle existe.
                foreach ($characters as $character) {
                    if ($character !== null) {
                        $repository = $entityManager->getRepository(Character::class);
                        $character = $repository->find($character);
                        $characterGame = null;
                        if ($character) {
                            // Si le personnage n'a pas encore de partie, on le lie à la partie qu'on modifie
                            $characterGame = $character->getGame();
                            if ($characterGame === null) {
                                $game->addCharacter($character);
                            } else {
                                $this->addFlash('alert', sprintf('%s %s n\'a pas été ajouté, il est déjà lié à une autre partie !', $character->getFirstName(), $character->getLastName()));
                            }
                        } else {
                            $this->addFlash('alert', 'Vous avez tenté d\'ajouter un personnage qui n\'existe pas');
                        }
                    }
                }

                // Envoi des modifications en BDD
                $entityManager->persist($game);
                $entityManager->flush();

                $this->addFlash('success', sprintf('La partie "%s" a été modifiée !', $game->getName()));
                return $this->redirectToRoute('app_game_list');
            }
        } else if ($game && $game->getUser()->getId() !== $userId) {
            $this->addFlash('alert', 'On ne peut pas modifier les parties des autres !');
            return $this->redirectToRoute('app_game_list');
        } else {
            $this->addFlash('alert', 'Cette partie n\'existe pas');
            return $this->redirectToRoute('app_game_list');
        }

        return $this->render('game/modify-game.html.twig', [
            'gameForm' => $form->createView(),]);
    }

    #[Route('joueur/voir-partie/{id}', name: 'app_game')]
    public function gameDetail(int $id): Response
    {
        // Récupération en BDD de la partie correspondant à l'id de l'url
        $entityManager = $this->doctrine->getManager();
        $repository = $entityManager->getRepository(Game::class);
        $game = $repository->find($id);

        // Récupération en BDD des résumés de la partie récupérée ci-dessus
        $repository = $entityManager->getRepository(Summary::class);
        $summaries = $repository->findBy(array('game' => $id));

        $userId = $this->getUser()->getId();

        // Récupération en BDD des personnages liés à l'utilisateur
        $repository = $entityManager->getRepository(Character::class);
        $characters = $repository->findBy(array('user' => $userId));
        // Récupération dans un tableau des identifiants de partie des personnages de l'utilisateur
        $charactersGamesIds = [];
        foreach ($characters as $character) {
            if ($character->getGame()) {
                $charactersGamesIds[] = $character->getGame()->getId();
            }
        }
        // Affichage de la page uniquement si la partie existe et que l'utilisateur en est le créateur ou qu'un de
        // ses personnages y participe.
        if ($game  && ($game->getUser()->getId() === $userId || in_array($id, $charactersGamesIds))) {
            return $this->render('game/game-detail.html.twig', ['game' => $game, 'summaries' => $summaries ]);
        } else if ($game && $game->getUser()->getId() !== $userId) {
            $this->addFlash('alert', 'On ne peut pas voir les parties des autres !');
        } else {
            $this->addFlash('alert', 'Cette partie n\'existe pas');
        }
        return $this->redirectToRoute('app_game_list');
    }
}
