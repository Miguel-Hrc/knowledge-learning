<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;

/**
 * Listens for AccessDeniedHttpException and renders a custom 403 error page.
 *
 * This listener intercepts exceptions of type AccessDeniedHttpException and
 * returns a Twig-rendered response instead of the default Symfony error page.
 */
class AccessDeniedExceptionListener
{
    /**
     * @var Environment Twig environment for rendering templates.
     */
    private Environment $twig;

    /**
     * @var RequestStack The request stack, allowing access to the current request if needed.
     */
    private RequestStack $requestStack;

    /**
     * Constructor.
     *
     * @param Environment $twig The Twig environment used to render templates.
     * @param RequestStack $requestStack The request stack to access the current request.
     */
    public function __construct(Environment $twig, RequestStack $requestStack)
    {
        $this->twig = $twig;
        $this->requestStack = $requestStack;
    }

    /**
     * Handles kernel exceptions.
     *
     * This method listens to the Symfony kernel exception event. If the exception
     * is an instance of AccessDeniedHttpException, it renders a custom 403 error page
     * and sets it as the response.
     *
     * @param ExceptionEvent $event The event containing the exception and request.
     */
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        // Only handle AccessDeniedHttpException
        if (!$exception instanceof AccessDeniedHttpException) {
            return;
        }

        // Render the custom 403 error page
        $content = $this->twig->render('bundles/TwigBundle/Exception/error403.html.twig');
        $response = new Response($content, Response::HTTP_FORBIDDEN);

        // Set the response on the event, overriding the default error page
        $event->setResponse($response);
    }
}