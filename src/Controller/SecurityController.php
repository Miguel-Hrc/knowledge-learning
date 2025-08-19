<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    private string $dataSource;

    public function __construct(ParameterBagInterface $params)
    {
        $this->dataSource = strtolower($params->get('app.data_source') ?? 'orm');
    }

    /**
     * Redirect user to the correct login page based on datasource
     */
    #[Route('/login-redirect', name: 'app_login_redirect')]
    public function loginRedirect(): Response
    {
        if ($this->dataSource === 'mongodb') {
            return $this->redirectToRoute('app_login_mongo');
        }

        return $this->redirectToRoute('app_login_sql');
    }

    /**
     * Login page for ORM (SQL)
     */
    #[Route('/login-sql', name: 'app_login_sql', methods: ['GET', 'POST'])]
    public function loginSql(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login_sql.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
            'dataSource' => $this->dataSource,
        ]);
    }

    /**
     * Login page for MongoDB
     */
    #[Route('/login-mongo', name: 'app_login_mongo', methods: ['GET', 'POST'])]
    public function loginMongo(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login_mongo.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
            'dataSource' => $this->dataSource,
        ]);
    }

    /**
     * Logout (shared for both)
     */
    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the firewall.');
    }
}