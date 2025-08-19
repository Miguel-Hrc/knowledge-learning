<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * MongoAuthenticator handles user authentication for MongoDB users.
 *
 * It supports login requests specifically on the '/login-mongo' route
 * and integrates CSRF protection, remember-me functionality, and
 * password verification.
 */
class MongoAuthenticator extends AbstractAuthenticator
{
    private MongoUserProvider $userProvider;
    private RouterInterface $router;
    private KernelInterface $kernel;

    /**
     * Constructor.
     *
     * @param MongoUserProvider $userProvider The user provider for MongoDB users
     * @param RouterInterface   $router       The router to generate redirects
     * @param KernelInterface   $kernel       The Symfony kernel (used for environment check)
     */
    public function __construct(MongoUserProvider $userProvider, RouterInterface $router, KernelInterface $kernel)
    {
        $this->userProvider = $userProvider;
        $this->router = $router;
        $this->kernel = $kernel;
    }

    /**
     * Determines if this authenticator should handle the given request.
     *
     * @param Request $request The current HTTP request
     *
     * @return bool|null True if this authenticator supports the request, false otherwise
     */
    public function supports(Request $request): ?bool
    {
        return $request->isMethod('POST') && $request->getPathInfo() === '/login-mongo';
    }

    /**
     * Authenticates the user by creating a Passport.
     *
     * @param Request $request The current HTTP request
     *
     * @return Passport The created passport containing credentials and badges
     *
     * @throws AuthenticationException If the email or password is missing
     */
    public function authenticate(Request $request): Passport
    {
        $email = $request->request->get('_username', '');
        $password = $request->request->get('_password', '');

        if (!$email || !$password) {
            throw new AuthenticationException('Email or password missing.');
        }

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
     * Redirects the user to the homepage.
     *
     * @param Request        $request      The current request
     * @param TokenInterface $token        The authenticated token
     * @param string         $firewallName The firewall name
     *
     * @return Response|null A redirect response to the homepage
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return new RedirectResponse($this->router->generate('app_home'));
    }

    /**
     * Called when authentication fails.
     *
     * Redirects the user back to the MongoDB login page.
     *
     * @param Request                 $request   The current request
     * @param AuthenticationException $exception The authentication exception
     *
     * @return Response|null A redirect response to the login page
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new RedirectResponse($this->router->generate('app_login_mongo'));
    }
}