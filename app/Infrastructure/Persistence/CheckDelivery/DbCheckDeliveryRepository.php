<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\CheckDelivery;

use App\Domain\CheckDelivery;
use App\Domain\CheckDeliveryRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\Expr\Join;

class DbCheckDeliveryRepository implements CheckDeliveryRepository
{
    public function __construct(protected EntityManager $entityManager)
    {
    }

    public function countAll(): int
    {
        return $this->entityManager->getRepository(CheckDelivery::class)->count([]);
    }

    public function countBy(array $criteria): int
    {
        return $this->entityManager->getRepository(CheckDelivery::class)->count($criteria);
    }

    public function getQueryBuilder()
    {
        return $this->entityManager->getRepository(CheckDelivery::class)->createQueryBuilder('cd');
    }

    public function findAll(): array
    {
        return $this->entityManager->getRepository(CheckDelivery::class)->findBy([], ['date' => 'ASC']);
    }

    public function findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null): array
    {
        return $this->entityManager->getRepository(CheckDelivery::class)->findBy($criteria, $orderBy, $limit, $offset);
    }

    public function findByDifference($amount, $count, $date)
    {
        $qb = $this->entityManager->getRepository(CheckDelivery::class)->createQueryBuilder('cd');
        $qb->leftJoin('cd.lines', 'l', Join::ON);
        $qb->where('cd.converted = false');
        $qb->orderBy('ABS(cd.amount - :amount)', 'ASC');
        $qb->addOrderBy('ABS(COUNT(l.id) - :count)', 'ASC');
        $qb->addOrderBy('ABS(DATE_DIFF(cd.date, :date))', 'ASC');
        $qb->setParameter('amount', $amount);
        $qb->setParameter('date', $date);
        $qb->setParameter('count', $count);
        $qb->groupBy('cd.id');
        return $qb->getQuery()->getResult();
    }

    public function findCheckDeliveryOfId(int $id): CheckDelivery | null
    {
        return $this->entityManager->getRepository(CheckDelivery::class)->find($id);
    }

    public function save(CheckDelivery $checkDelivery): void
    {
        $this->entityManager->persist($checkDelivery);
        $this->entityManager->flush();
    }
}
