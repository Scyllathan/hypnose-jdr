<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/annexes')]
class AnnexController extends AbstractController
{
    #[Route('/les-lieux-de-dl', name: 'app_places')]
    public function places(): Response
    {
        return $this->render('annex/places.html.twig');
    }

    #[Route('/les-7-tours', name: 'app_towers')]
    public function towers(): Response
    {
        return $this->render('annex/towers.html.twig');
    }

    #[Route('/methodes-d-introduction', name: 'app_intro_methods')]
    public function introductionMethods(): Response
    {
        return $this->render('annex/intro-methods.html.twig');
    }

    #[Route('/faune-et-flore', name: 'app_fauna_flora')]
    public function faunaAndFlora(): Response
    {
        return $this->render('annex/fauna-and-flora.html.twig');
    }
}
