<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\CheckDelivery;

use App\Domain\CheckDeliveryLine;
use App\Domain\CheckDeliveryLineRepository;
use Doctrine\ORM\EntityManager;

class DbCheckDeliveryLineRepository implements CheckDeliveryLineRepository
{
    public function __construct(protected EntityManager $entityManager)
    {
    }

    public function countAll(): int
    {
        return $this->entityManager->getRepository(CheckDeliveryLine::class)->count();
    }

    public function countBy(array $criteria): int
    {
        $qb = $this->entityManager->getRepository(CheckDeliveryLine::class)->createQueryBuilder('cdl');
        $qb->select('COUNT(cdl.id)');
        foreach ($criteria as $field => $value) {
            $qb->andWhere("cdl.{$field} = :{
field}")
               ->setParameter($field, $value);
        }
        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function getQueryBuilder()
    {
        return $this->entityManager->getRepository(CheckDeliveryLine::class)->createQueryBuilder('cdl');
    }

    public function findAll(): array
    {
        return $this->entityManager->getRepository(CheckDeliveryLine::class)->findBy([], ['date' => 'ASC']);
    }

    public function findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null): array
    {
        return $this->entityManager->getRepository(CheckDeliveryLine::class)
            ->findBy($criteria, $orderBy, $limit, $offset);
    }

    public function findCheckDeliveryLineOfId(int $id): CheckDeliveryLine | null
    {
        return $this->entityManager->getRepository(CheckDeliveryLine::class)->find($id);
    }

    public function save(CheckDeliveryLine $checkDeliveryLine): void
    {
        $this->entityManager->persist($checkDeliveryLine);
        $this->entityManager->flush();
    }
}
