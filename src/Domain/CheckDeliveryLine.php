<?php

namespace App\Domain;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\ManyToOne;

#[Entity, Table(name: 'check_deliveries_line')]
final class CheckDeliveryLine implements \JsonSerializable
{
    #[Id, Column(type: 'integer'), GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[Column(type: 'string', length: 255, nullable: false)]
    private string $checkNumber;

    #[Column(type: 'string', length: 255, nullable: true, options: ['collation' => 'nocase'])]
    private string $name;

    #[Column(type: 'string', length: 255, nullable: true)]
    private ?string $label;

    #[Column(type: 'float')]
    private float $amount;

    #[ManyToOne(targetEntity: CheckDelivery::class, inversedBy: 'lines')]
    private CheckDelivery $checkDelivery;

    public function __construct()
    {
    }

    public function setCheckNumber(string $checkNumber): void
    {
        $this->checkNumber = $checkNumber;
    }

    public function getCheckNumber(): string
    {
        return $this->checkNumber;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setLabel(?string $label): void
    {
        $this->label = $label;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setAmount(float $amount): void
    {
        $this->amount = $amount;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setCheckDelivery(CheckDelivery $checkDelivery): void
    {
        $this->checkDelivery = $checkDelivery;
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'checkNumber' => $this->checkNumber,
            'name' => $this->name,
            'label' => $this->label,
            'amount' => $this->amount,
            'checkDelivery' => $this->checkDelivery,
        ];
    }
}
