<?php

namespace App\Domain;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\OneToMany;

#[Entity, Table(name: 'check_deliveries')]
final class CheckDelivery implements \JsonSerializable
{
    #[Id, Column(type: 'integer'), GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[Column(type: 'datetimetz_immutable', nullable: false)]
    private \DateTimeImmutable $date;

    #[Column(type: 'float')]
    private float $amount;

    #[Column(type: 'boolean')]
    private bool $converted = false;

    /** @var  Collection<int, CheckDeliveryLine> */
    #[OneToMany(targetEntity: CheckDeliveryLine::class, mappedBy: 'checkDelivery')]
    private Collection $lines;

    public function __construct()
    {
        $this->lines = new ArrayCollection();
        $this->amount = 0;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setDate(\DateTimeImmutable $date): void
    {
        $this->date = $date;
    }

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    public function setAmount(float $amount): void
    {
        $this->amount = $amount;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setConverted(bool $converted): void
    {
        $this->converted = $converted;
    }

    public function isConverted(): bool
    {
        return $this->converted;
    }

    public function addLine(CheckDeliveryLine $line): void
    {
        $line->setCheckDelivery($this);
        $this->lines->add($line);
    }

    public function removeLine(CheckDeliveryLine $line): void
    {
        $this->lines->removeElement($line);
    }

    /** @return Collection<int, CheckDeliveryLine> */
    public function getLines(): Collection
    {
        return $this->lines;
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'date' => $this->date->format('Y-m-d'),
            'amount' => $this->amount,
            'lines' => $this->lines->toArray(),
        ];
    }
}
