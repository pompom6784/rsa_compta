<?php

declare(strict_types=1);

namespace App\Domain;

interface LineRepository
{
    /**
     * @return int
     */
    public function countAll(): int;

    /**
     * @return Line[]
     */
    public function findAll(): array;

    /**
     * @param array $criteria
     * @param array|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @return Line[]
    */
    public function findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null): array;

    /**
     * @param int $id
     * @return Line
     * @throws LineNotFoundException
     */
    public function findLineOfId(int $id): Line | null;
}
