<?php

declare(strict_types=1);

namespace App\Domain;

interface CheckDeliveryLineRepository
{
    /**
     * @return int
     */
    public function countAll(): int;

    /**
     * @return CheckDeliveryLine[]
     */
    public function findAll(): array;

    /**
     * @param array $criteria
     * @param array|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @return CheckDeliveryLine[]
    */
    public function findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null): array;

    /**
     * @param int $id
     * @return CheckDeliveryLine
     */
    public function findCheckDeliveryLineOfId(int $id): CheckDeliveryLine | null;
}
