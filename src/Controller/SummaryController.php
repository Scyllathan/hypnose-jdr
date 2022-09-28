<?php

namespace App\Controller;

use App\Entity\Character;
use App\Entity\Game;
use App\Entity\Summary;
use App\Form\SummaryType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SummaryController extends AbstractController
{
    public function __construct(private ManagerRegistry $doctrine) {}

    #[Route('/mj/nouveau-resume/{id}', name: 'app_new_summary')]
    public function index(Request $request, EntityManagerInterface $entityManager, int $id): Response
    {
        // Création du formulaire
        $summary = new Summary();
        $form = $this->createForm(SummaryType::class, $summary);
        $form->handleRequest($request);

        // Récupération de l'entité Game associé à l'id en GET
        $entityManager = $this->doctrine->getManager();
        $repository = $entityManager->getRepository(Game::class);
        $game = $repository->find($id);

        if ($form->isSubmitted() && $form->isValid()) {

            // Complétion de l'entité Summary et envoi en BDD
            $summary->setGame($game);
            $entityManager->persist($summary);
            $entityManager->flush();

            $this->addFlash('success', 'Résumé créé !');

            return $this->redirectToRoute('app_game', ['id' => $id]);
        }

        return $this->render('summary/index.html.twig', [
            'summaryForm' => $form->createView(), 'game' => $game]);
    }

    #[Route('/joueur/voir-resume/{id}', name: 'app_summary_detail')]
    public function summaryDetail(int $id): Response
    {
        // Récupération des données à afficher sur la page
        $entityManager = $this->doctrine->getManager();
        $repository = $entityManager->getRepository(Summary::class);
        $summary = $repository->find($id);

        // Récupération des données de vérification d'accès
        $userId = $this->getUser()->getId();
        $repository = $entityManager->getRepository(Character::class);
        $characters = $repository->findBy(array('user' => $userId));
        $charactersGamesIds = [];
        foreach ($characters as $character) {
            $charactersGamesIds[] = $character->getGame()->getId();
        }
        if ($summary){
            $summaryGameId = $summary->getGame()->getId();
        }

        // Vérifications d'accès et redirection + flash message
        if ($summary && ($summary->getGame()->getUser()->getId() === $userId || in_array($summaryGameId, $charactersGamesIds))) {
            return $this->render('summary/summary-detail.html.twig', [ 'summary' => $summary ]);
        } else if ($summary && $summary->getGame()->getUser()->getId() !== $userId) {
            $this->addFlash('alert', 'On ne peut pas voir les résumés des parties des autres !');
        } else {
            $this->addFlash('alert', 'Ce résumé de partie n\'existe pas');
        }

        return $this->redirectToRoute('index');
    }

    #[Route('/mj/modifier-resume/{id}', name: 'app_modify_summary')]
    public function modifySummary(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Création du formulaire
        $repository = $entityManager->getRepository(Summary::class);
        $summary = $repository->find($id);
        $form = $this->createForm(SummaryType::class, $summary);
        $form->handleRequest($request);

        // Vérifications d'accès et redirection + flash message
        if ($summary && $summary->getGame()->getUser()->getId() === $this->getUser()->getId()) {
            if ($form->isSubmitted() && $form->isValid()) {
                // Récupération des données modifiées et envoi en BDD
                $summary = $form->getData();
                $entityManager->persist($summary);
                $entityManager->flush();

                $this->addFlash('success', 'Résumé modifié !');
                return $this->redirectToRoute('app_game', ['id' => $summary->getGame()->getId()]);
            }
        } else if ($summary && $summary->getGame()->getUser()->getId() !== $this->getUser()->getId()) {
            $this->addFlash('alert', 'On ne peut pas modifier les résumés des autres !');
            return $this->redirectToRoute('app_game_list');
        } else {
            $this->addFlash('alert', 'Ce résumé n\'existe pas');
            return $this->redirectToRoute('app_game_list');
        }

        return $this->render('summary/modify-summary.html.twig', [
            'summaryForm' => $form->createView(), 'summary' => $summary,]);
    }

    #[Route('/mj/supprimer-resume/{id}', name: 'app_delete_summary')]
    public function deleteSummary(int $id): Response
    {
        $entityManager = $this->doctrine->getManager();
        $repository = $entityManager->getRepository(Summary::class);
        $summary = $repository->find($id);

        if ($summary && $summary->getGame()->getUser()->getId() === $this->getUser()->getId()) {
            $repository->remove($summary, true);
            $this->addFlash('success', sprintf('"%s" a bien été supprimé !', $summary->getTitle()));
            return $this->redirectToRoute('app_game', ['id' => $summary->getGame()->getId()]);
        } else if ($summary && $summary->getGame()->getUser()->getId() !== $this->getUser()->getId()) {
            $this->addFlash('alert', 'On ne peut pas modifier les résumés des autres !');
            return $this->redirectToRoute('app_game_list');
        } else {
            $this->addFlash('alert', 'Ce résumé n\'existe pas');
            return $this->redirectToRoute('app_game_list');
        }
    }
}
