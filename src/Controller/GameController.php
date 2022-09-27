<?php

namespace App\Controller;

use App\Entity\Character;
use App\Entity\Game;
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
}
