<?php

// src/Controller/ContactController.php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Entity\Contact;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Doctrine\ORM\EntityManagerInterface;

class ContactController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/contact/preview/{email}", name="contact_preview")
     * Display User Page
     */
    public function preview(Request $request, $email): Response{
        $session = $request->getSession();

        // Si l'utilisateur est déjà connecté, redirigez-le vers la page d'accueil
        if ($session->get('user')) {
            // Get User
            $user = $session->get('user');
            
            // Get Contact List
            $contactList = $this->entityManager->getRepository(Utilisateur::class)->getContactList($user);

            $contactUser = $this->entityManager->getRepository(Utilisateur::class)->findOneBy([
                'email' => $email
            ]);

            // Get Contact List of contact
            $contactListOfContact = $this->entityManager->getRepository(Utilisateur::class)->getContactList($contactUser);

            // Get Common Contact List
            $commonContact = $this->entityManager->getRepository(Contact::class)->getCommonContactList($contactList, $contactListOfContact);

            // Render User Page
            return $this->render('contact/preview_contact.html.twig', [
                'user' => $user,
                'userContactList' => $contactList,
                'contact' => $contactUser,
                'contactContactList' => $contactListOfContact,
                'commonContactList' => $commonContact,
                'nbCommonContact' => count($commonContact)
            ]);
        }

        // Sinon, redirigez-le vers la page de connexion
        return $this->redirectToRoute('home');
    }
}