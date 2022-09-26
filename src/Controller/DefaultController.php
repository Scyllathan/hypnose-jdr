<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->render('default/index.html.twig');
    }

    #[Route('/contexte', name: 'contexte')]
    public function context() : Response
    {
        return $this->render('default/context.html.twig');
    }

    #[Route('/regles', name: 'regles')]
    public function rules() : Response
    {
        return $this->render('default/rules.html.twig');
    }
}
