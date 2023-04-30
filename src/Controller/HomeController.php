<?php

// src/Controller/HomeController.php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(Request $request, SessionInterface $session): Response
    {
        $session = $request->getSession();

        // Si l'utilisateur est déjà connecté, redirigez-le vers la page d'accueil
        if ($session->get('user')) {
            return $this->redirectToRoute('user_profile');
        }
        return $this->render('home/index.html.twig');
    }
}
