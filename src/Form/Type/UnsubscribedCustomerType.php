<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Form\Type;

use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;

final class UnsubscribedCustomerType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('email', EmailType::class, [
            'label' => 'setono_sylius_abandoned_cart.form.unsubscribed_customer.email',
        ]);
    }
}
