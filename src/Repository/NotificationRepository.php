<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Repository;

use DateTimeInterface;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

class NotificationRepository extends EntityRepository implements NotificationRepositoryInterface
{
    public function removeOlderThan(DateTimeInterface $threshold): void
    {
        $this->createQueryBuilder('o')
            ->delete()
            ->andWhere('o.createdAt <= :threshold')
            ->setParameter('threshold', $threshold)
            ->getQuery()
            ->execute()
        ;
    }
}
