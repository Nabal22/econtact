<?php

// src/Controller/UserController.php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Entity\Contact;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Doctrine\ORM\EntityManagerInterface;

class UserController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/profile", name="user_profile")
     * Display User Page
     */
    public function profile(Request $request, AuthenticationUtils $authenticationUtils): Response{
        $session = $request->getSession();
        // Si l'utilisateur est déjà connecté, redirigez-le vers la page d'accueil
        if ($session->get('user')) {
            $user = $this->entityManager->getRepository(Utilisateur::class)->findOneBy([
                'id_nom' => $session->get('user')
            ]);

            // Get Contact List
            $contactList = $this->entityManager->getRepository(Utilisateur::class)->getContactList($user);

            // Render User Page
            return $this->render('user/user.html.twig', [
                'user' => $user,
                'contactList' => $contactList
            ]);
        }

        // Sinon, redirigez-le vers la page de connexion
        return $this->redirectToRoute('home');
    }
}