<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Controller\Action;

use Setono\SyliusAbandonedCartPlugin\Factory\UnsubscribedCustomerFactoryInterface;
use Setono\SyliusAbandonedCartPlugin\Hasher\EmailHasherInterface;
use Setono\SyliusAbandonedCartPlugin\Repository\UnsubscribedCustomerRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;
use Webmozart\Assert\Assert;

final class UnsubscribeCustomerAction
{
    public function __construct(
        private readonly EmailHasherInterface $emailHasher,
        private readonly UnsubscribedCustomerRepositoryInterface $unsubscribedCustomerRepository,
        private readonly UnsubscribedCustomerFactoryInterface $unsubscribedCustomerFactory,
        private readonly Environment $twig,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $error = null;

        try {
            $email = $request->query->get('email') ?? $request->request->get('email');
            Assert::stringNotEmpty($email, 'setono_sylius_abandoned_cart.ui.no_email_provided');
            $email = strtolower($email);

            $hash = $request->query->get('hash') ?? $request->request->get('hash');
            Assert::stringNotEmpty($hash, 'setono_sylius_abandoned_cart.ui.no_hash_provided');

            Assert::same($hash, $this->emailHasher->hash($email), 'setono_sylius_abandoned_cart.ui.invalid_hash');

            Assert::false(
                $this->unsubscribedCustomerRepository->isUnsubscribed($email),
                'setono_sylius_abandoned_cart.ui.email_already_unsubscribed',
            );

            $this->unsubscribedCustomerRepository->add($this->unsubscribedCustomerFactory->createWithEmail($email));
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        return new Response($this->twig->render('@SetonoSyliusAbandonedCartPlugin/shop/unsubscribe.html.twig', [
            'error' => $error,
        ]));
    }
}
