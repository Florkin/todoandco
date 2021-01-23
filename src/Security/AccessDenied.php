<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;

class AccessDenied implements AccessDeniedHandlerInterface
{
    /**
     * @var RouterInterface
     */
    private $router;
    /**
     * @var FlashBagInterface
     */
    private $flashBag;

    /**
     * AccessDenied constructor.
     * @param RouterInterface $router
     * @param FlashBagInterface $flashBag
     */
    public function __construct(RouterInterface $router, FlashBagInterface $flashBag)
    {
        $this->router = $router;
        $this->flashBag = $flashBag;
    }

    public function handle(Request $request, AccessDeniedException $accessDeniedException)
    {
        $this->flashBag->add('error', 'Action non autorisée !');

        return new RedirectResponse(
            $this->router->generate('homepage'),
            302
        );
    }
}