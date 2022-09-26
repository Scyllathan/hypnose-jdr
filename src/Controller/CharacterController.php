<?php

namespace App\Controller;

use App\Entity\Character;
use App\Form\CharacterType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CharacterController extends AbstractController
{
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
}
