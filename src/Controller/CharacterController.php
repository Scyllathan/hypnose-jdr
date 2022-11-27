<?php

namespace App\Controller;

use App\Entity\Character;
use App\Form\CharacterType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

#[Route('/joueur')]
class CharacterController extends AbstractController
{
    public function __construct(private ManagerRegistry $doctrine) {}

    #[Route('/nouveau-personnage', name: 'app_character_new')]
    public function newCharacter(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Création d'un nouveau personnage et du formulaire vide associé
        $character = new Character();
        $form = $this->createForm(CharacterType::class, $character);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Contrôle de la condition "le total des caractéristiques doit être égal à 80"
            $totalCharacs = $character->getStamina() + $character->getStrength() + $character->getAgility() +
                $character->getSpeed() + $character->getIntelligence() + $character->getResilience() +
                $character->getCharisma() + $character->getLuck();
            if ($totalCharacs !== 80) {
                $this->addFlash('alert', 'La somme de vos caractéristiques doit être égale à 80 !');
                return $this->render('character/index.html.twig', [
                    'characterForm' => $form->createView(),]);
            }
            // Ajout de l'utilisateur au personnage et envoi en BDD
            $user = $this->getUser();
            $character->setUser($user);
            $character->setMp($user->getId());
            $entityManager->persist($character);
            $entityManager->flush();

            $this->addFlash('success', 'Personnage créé !');
            return $this->redirectToRoute('app_character_list');
        }

        return $this->render('character/index.html.twig', [
            'characterForm' => $form->createView(),]);
    }

    #[Route('/mes-personnages', name: 'app_character_list')]
    public function characterList(Request $request, PaginatorInterface $paginator): Response
    {
        // Récupération des personnages de l'utilisateur pour affichage
        $entityManager = $this->doctrine->getManager();
        $repository = $entityManager->getRepository(Character::class);
        $userId = $this->getUser()->getId();
        $characters = $repository->findBy(array('user' => $userId));

        $paginatedCharacters = $paginator->paginate(
            $characters, // Requête contenant les données à paginer
            $request->query->getInt('page', 1), // Numéro de la page en cours, passé dans l'URL, 1 si aucune page
            10 // Nombre de résultats par page
        );

        return $this->render('character/character-list.html.twig', ['characters' => $paginatedCharacters]);
    }

    #[Route('/personnages-publics', name: 'app_character_public_list')]
    public function characterPublicList(): Response
    {
        return $this->render('character/character-public-list.html.twig',);
    }

    #[Route('/voir-personnage/{id}', name: 'app_character')]
    public function characterDetail(int $id): Response
    {
        // Récupération en BDD du personnage correspondant à l'id de l'url
        $entityManager = $this->doctrine->getManager();
        $repository = $entityManager->getRepository(Character::class);
        $character = $repository->find($id);

        // Ajustement de la mise en page des trois éléments textarea pour qu'elle soit conservée en html
        if ($character) {
            $story = nl2br($character->getStory(), false);
            $powers = nl2br($character->getPowers(), false);
            $bag = nl2br($character->getBag(), false);
        }

        $userId = $this->getUser()->getId();

        // Vérification que le personnage existe et que l'utilisateur en est le créateur ou que le personnage est public
        if ($character  && ($character->getUser()->getId() === $userId || $character->isIsPublic())) {
            return $this->render('character/character-detail.html.twig', ['character' => $character, 'story' =>
                $story, 'powers' => $powers, 'bag' => $bag]);
        } else if ($character && $character->getUser()->getId() !== $userId) {
            $this->addFlash('alert', 'On ne peut pas voir les personnages des autres !');
        } else {
            $this->addFlash('alert', 'Ce personnage n\'existe pas');
        }

        return $this->redirectToRoute('app_character_list');
    }

    #[Route('/supprimer-personnage/{id}', name: 'app_del_character')]
    public function deleteCharacter(int $id): Response
    {
        // Récupération en BDD du personnage correspondant à l'id de l'url
        $entityManager = $this->doctrine->getManager();
        $repository = $entityManager->getRepository(Character::class);
        $character = $repository->find($id);

        $userId = $this->getUser()->getId();

        // Vérification que le personnage existe et que l'utilisateur en est le créateur
        if ($character && $character->getUser()->getId() === $userId) {
            // Suppression du personnage en BDD
            $repository->remove($character, true);
            $this->addFlash('success', sprintf('%s %s a bien été supprimé !', $character->getFirstName(),
                $character->getLastName()));
        } else if ($character && $character->getUser()->getId() !== $userId) {
            $this->addFlash('alert', 'On ne peut pas supprimer les personnages des autres !');
        } else {
            $this->addFlash('alert', 'Ce personnage n\'existe pas');
        }

        return $this->redirectToRoute('app_character_list');
    }

    #[Route('/modifier-personnage/{id}', name:'app_modify_character')]
    public function modifyCharacter(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Récupération en BDD du personnage correspondant à l'id de l'url et création du formulaire correspondant
        $repository = $entityManager->getRepository(Character::class);
        $character = $repository->find($id);
        $form = $this->createForm(CharacterType::class, $character);
        $form->handleRequest($request);

        $userId = $this->getUser()->getId();

        // Vérification que le personnage existe et que l'utilisateur en est le créateur
        if ($character && $character->getUser()->getId() === $userId) {
            if ($form->isSubmitted() && $form->isValid()) {
                // Mise à jour du personnage et envoi en BDD
                $character = $form->getData();
                $entityManager->persist($character);
                $entityManager->flush();

                $this->addFlash('success', 'Personnage modifié !');
                return $this->redirectToRoute('app_character_list');
            }
        } else if ($character && $character->getUser()->getId() !== $userId) {
            $this->addFlash('alert', 'On ne peut pas modifier les personnages des autres !');
            return $this->redirectToRoute('app_character_list');
        } else {
            $this->addFlash('alert', 'Ce personnage n\'existe pas');
            return $this->redirectToRoute('app_character_list');
        }

        return $this->render('character/modify-character.html.twig', [
            'characterForm' => $form->createView(), 'character' => $character]);
    }

    #[Route('/quitter-partie/{id}', name: 'app_quit_game')]
    public function quitGame(int $id): Response
    {
        // Récupération en BDD du personnage correspondant à l'id de l'url
        $entityManager = $this->doctrine->getManager();
        $repository = $entityManager->getRepository(Character::class);
        $character = $repository->find($id);

        $userId = $this->getUser()->getId();

        // Vérification que le personnage existe, que l'utilisateur en est le créateur et que le personnage
        // possède une partie
        if ($character && $character->getUser()->getId() === $userId && $character->getGame() !== null) {
            // Suppression du contenu de la colonne game dans la table du personnage
            $character->setGame(null);
            $entityManager->persist($character);
            $entityManager->flush();
            $this->addFlash('success', 'Votre personnage a quitté sa partie');
        } elseif ($character && $character->getUser()->getId() === $userId && $character->getGame() === null) {
            $this->addFlash('alert', 'Votre personnage n\'a pas de partie à quitter !');
        } else if ($character && $character->getUser()->getId() !== $userId) {
            $this->addFlash('alert', 'On ne peut pas faire quitter sa partie au personnage d\'un autre !');
        } else {
            $this->addFlash('alert', 'Ce personnage n\'existe pas');
        }

        return $this->redirectToRoute('app_character_list');
    }

    #[Route('/character-list-json')]
    public function clientListJson(): Response
    {
        $entityManager = $this->doctrine->getManager();
        $repository = $entityManager->getRepository(Character::class);
        $characters = $repository->findBy(array('isPublic' => true));

        $encoder = new JsonEncoder();
        $defaultContext = [
            AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object, $format, $context) {
                return $object->getId();
            },
        ];
        $normalizer = new ObjectNormalizer(null, null, null, null, null, null, $defaultContext);

        $serializer = new Serializer([$normalizer], [$encoder]);
        $json = $serializer->serialize($characters, 'json', [AbstractNormalizer::IGNORED_ATTRIBUTES => ['disease',
            'age', 'story', 'powers', 'money', 'bag', 'stamina', 'strength', 'agility', 'speed', 'charisma', 'intelligence',
            'resilience', 'luck', 'user', 'summaries'
            ]]);

        return $this->render('character/character-list-json.html.twig', [ 'json' => $json ]);
    }
}
