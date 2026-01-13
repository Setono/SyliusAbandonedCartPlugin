<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Controller\Action;

use Doctrine\Persistence\ManagerRegistry;
use Setono\Doctrine\ORMTrait;
use Setono\SyliusAbandonedCartPlugin\Repository\NotificationRepositoryInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class RecoverCartAction
{
    use ORMTrait;

    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly NotificationRepositoryInterface $notificationRepository,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly RequestStack $requestStack,
        ManagerRegistry $managerRegistry,
    ) {
        $this->managerRegistry = $managerRegistry;
    }

    public function __invoke(Request $request, string $tokenValue): Response
    {
        $cart = $this->orderRepository->findCartByTokenValue($tokenValue);

        if (null === $cart) {
            $this->addFlash('error', 'setono_sylius_abandoned_cart.ui.cart_recovery_link_expired');

            return new RedirectResponse($this->urlGenerator->generate('sylius_shop_homepage'));
        }

        $notification = $this->notificationRepository->findOneByOrder($cart);

        if (null !== $notification) {
            $notification->setLastClickedAt(new \DateTime());
            $this->getManager($notification)->flush();
        }

        $parameters = array_merge(
            $request->query->all(),
            ['tokenValue' => $tokenValue],
        );

        return new RedirectResponse(
            $this->urlGenerator->generate('sylius_shop_cart_summary', $parameters),
        );
    }

    private function addFlash(string $type, string $message): void
    {
        $session = $this->requestStack->getSession();

        if ($session instanceof FlashBagAwareSessionInterface) {
            $session->getFlashBag()->add($type, $message);
        }
    }
}
