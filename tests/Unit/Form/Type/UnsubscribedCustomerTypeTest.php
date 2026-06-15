<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Tests\Unit\Form\Type;

use PHPUnit\Framework\Attributes\Test;
use Setono\SyliusAbandonedCartPlugin\Form\Type\UnsubscribedCustomerType;
use Setono\SyliusAbandonedCartPlugin\Model\UnsubscribedCustomer;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;

final class UnsubscribedCustomerTypeTest extends TypeTestCase
{
    #[Test]
    public function it_maps_the_submitted_email_to_the_model(): void
    {
        $model = new UnsubscribedCustomer();

        $form = $this->factory->create(UnsubscribedCustomerType::class, $model);
        $form->submit(['email' => 'john@example.com']);

        self::assertTrue($form->isSynchronized());
        self::assertSame('john@example.com', $model->getEmail());
    }

    /**
     * @return list<PreloadedExtension>
     */
    protected function getExtensions(): array
    {
        return [
            new PreloadedExtension(
                [new UnsubscribedCustomerType(UnsubscribedCustomer::class, ['setono_sylius_abandoned_cart'])],
                [],
            ),
        ];
    }
}
