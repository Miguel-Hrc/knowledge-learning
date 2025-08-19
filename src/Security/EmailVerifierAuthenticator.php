<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;

/**
 * Authenticator used to handle email verification for users.
 *
 * This authenticator is intended for use with email verification flows and does not support
 * standard authentication via username/password.
 */
class EmailVerifierAuthenticator extends AbstractAuthenticator
{
    /**
     * Determines whether this authenticator should be used for the given request.
     *
     * @param Request $request The current HTTP request
     *
     * @return bool Always returns false because this authenticator is not used directly for requests
     */
    public function supports(Request $request): bool
    {
        return false;
    }

    /**
     * Throws an exception because this authenticator should not be called via authenticate().
     *
     * @param Request $request The current HTTP request
     *
     * @throws \LogicException Always thrown to indicate this method should not be used
     */
    public function authenticate(Request $request): Passport
    {
        throw new \LogicException('This authenticator should not be used via authenticate().');
    }

    /**
     * Creates a self-validating passport for a given user.
     *
     * @param UserInterface $user The user to create a passport for
     *
     * @return Passport A self-validating passport containing a UserBadge
     */
    public function createPassport(UserInterface $user): Passport
    {
        return new SelfValidatingPassport(new UserBadge($user->getUserIdentifier(), fn () => $user));
    }

    /**
     * Called on authentication success.
     *
     * @param Request $request The current HTTP request
     * @param TokenInterface $token The authenticated security token
     * @param string $firewallName The name of the firewall
     *
     * @return \Symfony\Component\HttpFoundation\Response|null Returns null to continue the request
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?\Symfony\Component\HttpFoundation\Response
    {
        return null; 
    }

    /**
     * Called on authentication failure.
     *
     * @param Request $request The current HTTP request
     * @param AuthenticationException $exception The exception that caused authentication failure
     *
     * @return \Symfony\Component\HttpFoundation\Response|null Returns null to continue the request
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?\Symfony\Component\HttpFoundation\Response
    {
        return null;
    }
}