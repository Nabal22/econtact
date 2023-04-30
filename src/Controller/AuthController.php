<?php

// src/Controller/AuthController.php

namespace App\Controller;

use App\Form\LoginFormType;
use App\Form\RegisterType;

use App\Entity\Utilisateur;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use Doctrine\ORM\EntityManagerInterface;


class AuthController extends AbstractController
{

    private $entityManager;
    
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/login", name="app_login")
     */
    public function login(Request $request, SessionInterface $session): Response
    {
        $session = $request->getSession();

        // Si l'utilisateur est déjà connecté, redirigez-le vers la page d'accueil
        if ($session->get('user')) {
            return $this->redirectToRoute('user_profile');
        }

        $user = new Utilisateur();
        // Créez un formulaire de connexion Symfony
        $form = $this->createForm(LoginFormType::class, $user);

        // Récupérez les données du formulaire
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérez les données du formulaire
            $data = $form->getData();

            // Récupérez l'utilisateur correspondant au nom d'utilisateur fourni
            $user = $this->entityManager->getRepository(Utilisateur::class)->findOneBy([
                'email' => $data->getEmail(),
            ]);

            // Vérifiez si le mot de passe fourni correspond au mot de passe de l'utilisateur
            if (!$user) { 
                // Si le nom d'utilisateur ou le mot de passe est incorrect, redirigez l'utilisateur vers la page de connexion
                return $this->render('auth/login.html.twig', [
                    'form' => $form->createView(),
                    'error' => 'Nom d\'utilisateur inconnu',
                ]);
            }// si le nom d'utilsateur est correct mais que le mot de passe est incorrect on renvoie une erreur mot de passe
            else if ($user && ($data->getNum() != $user->getNum())){
                return $this->render('auth/login.html.twig', [
                    'form' => $form->createView(),
                    'error' => 'Mot de passe incorrect',
                ]);
            }

            // Stockez l'utilisateur dans la session
            $session->set('user', $user);
            // Redirigez l'utilisateur vers la page d'accueil
            try {
                return $this->redirectToRoute('user_profile');
            } catch (\Exception $e) {
                return $this->render('auth/login.html.twig', [
                    'form' => $form->createView(),
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Affichez la vue Twig avec le formulaire de connexion et les erreurs
        return $this->render('auth/login.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/register", name="app_register")
     */
    public function register(Request $request, ValidatorInterface $validator): Response
    {
        // Créer un nouvel objet utilisateur
        $user = new Utilisateur();

        // Créer un formulaire d'inscription Symfony
        $form = $this->createForm(RegisterType::class, $user);

        // Récupérer les données du formulaire
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $validator->validate($user)) {
            // Récupérer les données du formulaire
            $data = $form->getData();

            // Enregistrer l'utilisateur dans la base de données
            $entityManager = $this->entityManager;
            $entityManager->persist($user);
            $entityManager->flush();

            // Rediriger l'utilisateur vers la page de connexion
            return $this->redirectToRoute('app_login');
        }
        else{
            $errors = $validator->validate($user);
            return $this->render('auth/register.html.twig', [
                'form' => $form->createView(),
                'errors' => $errors
            ]);
        }
        
        return $this->render('auth/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout(Request $request, SessionInterface $session): Response
    {
        $session = $request->getSession();
        $session->remove('user');
        return $this->redirectToRoute('app_login');

    }
}
