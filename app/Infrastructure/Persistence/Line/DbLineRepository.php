<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Line;

use App\Domain\Line;
use App\Domain\LineRepository;
use Doctrine\ORM\EntityManager;

class DbLineRepository implements LineRepository
{
    public function __construct(protected EntityManager $entityManager)
    {
    }

    public function countAll(): int
    {
        return $this->entityManager->getRepository(Line::class)->count([]);
    }

    public function countBy(array $criteria): int
    {
        return $this->entityManager->getRepository(Line::class)->count($criteria);
    }

    public function getQueryBuilder()
    {
        return $this->entityManager->getRepository(Line::class)->createQueryBuilder('l');
    }

    public function findAll(): array
    {
        return $this->entityManager->getRepository(Line::class)->findBy([], ['date' => 'ASC']);
    }

    public function findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null): array
    {
        return $this->entityManager->getRepository(Line::class)->findBy($criteria, $orderBy, $limit, $offset);
    }

    public function findLineOfId(int $id): Line | null
    {
        return $this->entityManager->getRepository(Line::class)->find($id);
    }

    public function save(Line $line): void
    {
        $this->entityManager->persist($line);
        $this->entityManager->flush();
    }

    public function delete(Line $line): void
    {
        $this->entityManager->remove($line);
        $this->entityManager->flush();
    }
}
