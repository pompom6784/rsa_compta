<?php

declare(strict_types=1);

namespace App\Domain;

interface CheckDeliveryRepository
{
    /**
     * @return int
     */
    public function countAll(): int;

    /**
     * @return CheckDelivery[]
     */
    public function findAll(): array;

    /**
     * @param array $criteria
     * @param array|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @return CheckDelivery[]
    */
    public function findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null): array;

    /**
     * @param int $id
     * @return CheckDelivery
     */
    public function findCheckDeliveryOfId(int $id): CheckDelivery | null;
}
