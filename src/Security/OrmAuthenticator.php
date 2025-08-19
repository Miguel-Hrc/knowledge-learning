<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * OrmAuthenticator handles authentication for users stored in a SQL database using ORM.
 *
 * Extends Symfony's AbstractAuthenticator and supports CSRF protection and "remember me" functionality.
 */
class OrmAuthenticator extends AbstractAuthenticator
{
    #[Autowire(service: 'security.user.provider.concrete.orm_user_provider')]
    private UserProviderInterface $userProvider;
    
    private RouterInterface $router;
    private KernelInterface $kernel;

    /**
     * Constructor.
     *
     * @param UserProviderInterface $userProvider The user provider for loading users
     * @param RouterInterface $router             The router for generating redirect URLs
     * @param KernelInterface $kernel             The kernel for environment detection
     */
    public function __construct(UserProviderInterface $userProvider, RouterInterface $router, KernelInterface $kernel)
    {
        $this->userProvider = $userProvider;
        $this->router = $router;
        $this->kernel = $kernel;
    }

    /**
     * Determines if the authenticator should handle the given request.
     *
     * @param Request $request The current HTTP request
     *
     * @return bool|null True if this authenticator should be used, false otherwise
     */
    public function supports(Request $request): ?bool
    {
        return $request->isMethod('POST') && $request->getPathInfo() === '/login-sql';
    }

    /**
     * Authenticates the user by creating a Passport object.
     *
     * @param Request $request The current HTTP request
     *
     * @return Passport The authentication passport
     *
     * @throws AuthenticationException If required credentials are missing
     */
    public function authenticate(Request $request): Passport
    {
        $email = $request->request->get('_username', '');
        $password = $request->request->get('_password', '');

        $badges = [new RememberMeBadge()];

        if ($this->kernel->getEnvironment() !== 'test') {
            $badges[] = new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token'));
        }

        return new Passport(
            new UserBadge($email, fn($userIdentifier) => $this->userProvider->loadUserByIdentifier($userIdentifier)),
            new PasswordCredentials($password),
            $badges
        );
    }

    /**
     * Called when authentication succeeds.
     *
     * @param Request $request       The current HTTP request
     * @param TokenInterface $token  The authenticated token
     * @param string $firewallName   The name of the firewall used
     *
     * @return Response|null A redirect response to the home page
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return new RedirectResponse($this->router->generate('app_home'));
    }

    /**
     * Called when authentication fails.
     *
     * @param Request $request               The current HTTP request
     * @param AuthenticationException $exception The exception that caused the failure
     *
     * @return Response|null A redirect response to the login page
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new RedirectResponse($this->router->generate('app_login'));
    }
}