<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Authenticator\AuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

/**
 * Fallback authenticator that delegates authentication to either the ORM or MongoDB authenticator
 * based on the login route.
 *
 * This allows a single security firewall to support two different user sources.
 */
class FallbackAuthenticator extends AbstractAuthenticator
{
    private AuthenticatorInterface $ormAuthenticator;
    private AuthenticatorInterface $mongoAuthenticator;

    /**
     * Constructor.
     *
     * @param AuthenticatorInterface $ormAuthenticator   Authenticator for SQL/ORM users
     * @param AuthenticatorInterface $mongoAuthenticator Authenticator for MongoDB users
     */
    public function __construct(
        AuthenticatorInterface $ormAuthenticator,
        AuthenticatorInterface $mongoAuthenticator
    ) {
        $this->ormAuthenticator = $ormAuthenticator;
        $this->mongoAuthenticator = $mongoAuthenticator;
    }

    /**
     * Determines if this authenticator should handle the current request.
     *
     * @param Request $request The current HTTP request
     *
     * @return bool True if the request is for /login-sql or /login-mongo and is a POST
     */
    public function supports(Request $request): bool
    {
        return in_array($request->getPathInfo(), ['/login-sql', '/login-mongo'], true)
            && $request->isMethod('POST');
    }

    /**
     * Delegates authentication to the appropriate authenticator based on the request path.
     *
     * @param Request $request The current HTTP request
     *
     * @return Passport The passport returned by the delegated authenticator
     *
     * @throws AuthenticationException If no authenticator supports the request
     */
    public function authenticate(Request $request): Passport
    {
        if ($request->getPathInfo() === '/login-sql') {
            return $this->ormAuthenticator->authenticate($request);
        }

        if ($request->getPathInfo() === '/login-mongo') {
            return $this->mongoAuthenticator->authenticate($request);
        }

        throw new AuthenticationException('No authenticator supports this request.');
    }

    /**
     * Handles authentication success by delegating to the appropriate authenticator.
     *
     * @param Request $request       The current HTTP request
     * @param TokenInterface $token  The authenticated security token
     * @param string $firewallName   The name of the firewall
     *
     * @return Response|null The response returned by the delegated authenticator or null
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        if ($request->getPathInfo() === '/login-sql') {
            return $this->ormAuthenticator->onAuthenticationSuccess($request, $token, $firewallName);
        }

        if ($request->getPathInfo() === '/login-mongo') {
            return $this->mongoAuthenticator->onAuthenticationSuccess($request, $token, $firewallName);
        }

        return null;
    }

    /**
     * Handles authentication failure by delegating to the appropriate authenticator.
     *
     * @param Request $request           The current HTTP request
     * @param AuthenticationException $exception The exception that caused the failure
     *
     * @return Response A response indicating authentication failure
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        if ($request->getPathInfo() === '/login-sql') {
            return $this->ormAuthenticator->onAuthenticationFailure($request, $exception);
        }

        if ($request->getPathInfo() === '/login-mongo') {
            return $this->mongoAuthenticator->onAuthenticationFailure($request, $exception);
        }

        return new Response('Authentication failed.', Response::HTTP_UNAUTHORIZED);
    }
}