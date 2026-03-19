<?php

namespace App\Domain;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\UniqueConstraint;

#[Entity, Table(name: 'lines')]
#[UniqueConstraint(name: 'line_unique', columns: ['type', 'date', 'name', 'label', 'amount', 'description'])]
final class Line implements \JsonSerializable
{
    #[Id, Column(type: 'integer'), GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[Column(type: 'string', length: 255, nullable: true)]
    private ?string $type;

    #[Column(type: 'datetimetz_immutable', nullable: false)]
    private \DateTimeImmutable $date;

    #[Column(type: 'string', length: 255, nullable: true, options: ['collation' => 'nocase'])]
    private ?string $name;

    #[Column(type: 'string', length: 255, nullable: true)]
    private ?string $label;

    #[Column(type: 'float')]
    private float $amount;

    #[Column(type: 'simple_array', nullable: true)]
    private array $breakdown;

    #[Column(type: 'text', nullable: true)]
    private ?string $description;

    #[Column(type: 'float', nullable: true)]
    public ?float $breakdownPlaneRenewal;

    #[Column(type: 'float', nullable: true)]
    public ?float $breakdownCustomerFees;

    #[Column(type: 'float', nullable: true)]
    public ?float $breakdownRSAContribution;

    #[Column(type: 'float', nullable: true)]
    public ?float $breakdownRSANavContribution;

    #[Column(type: 'float', nullable: true)]
    public ?float $breakdownFollowUpNav;

    #[Column(type: 'float', nullable: true)]
    public ?float $breakdownInternalTransfer;

    #[Column(type: 'float', nullable: true)]
    public ?float $breakdownPenRefund;

    #[Column(type: 'float', nullable: true)]
    public ?float $breakdownMeeting;

    #[Column(type: 'float', nullable: true)]
    public ?float $breakdownPaypalFees;

    #[Column(type: 'float', nullable: true)]
    public ?float $breakdownSogecomFees;

    #[Column(type: 'float', nullable: true)]
    public ?float $breakdownOsac;

    #[Column(type: 'float', nullable: true)]
    public ?float $breakdownOther;

    #[Column(type: 'float', nullable: true)]
    public ?float $breakdownDonation;

    #[Column(type: 'float', nullable: true)]
    public ?float $breakdownVibrationDebit;

    #[Column(type: 'float', nullable: true)]
    public ?float $breakdownVibrationCredit;

    public function __construct()
    {
        $this->breakdown = [];
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getType(): string | null
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): void
    {
        $this->date = $date;
    }

    public function getName(): string | null
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getLabel(): string | null
    {
        return $this->label;
    }

    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): void
    {
        $this->amount = $amount;
    }

    public function getDebit(): float | null
    {
        return $this->amount < 0 ? $this->amount : null;
    }

    public function getCredit(): float | null
    {
        return $this->amount > 0 ? $this->amount : null;
    }

    public function getBreakdown(): array
    {
        return $this->breakdown;
    }

    public function setBreakdown(array $breakdown): void
    {
        $this->breakdown = $breakdown;
    }

    public function addBreakdown(string $breakdown): void
    {
        $this->breakdown[] = $breakdown;
    }

    public function getDescription(): string | null
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function __set($name, $value)
    {
        $this->$name = $value;
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'date' => $this->date,
            'name' => $this->name,
            'label' => $this->label,
            'debit' => $this->amount < 0 ? $this->amount : null,
            'credit' => $this->amount > 0 ? $this->amount : null,
            'breakdown' => $this->breakdown,
            'breakdownPlaneRenewal' => $this->breakdownPlaneRenewal,
            'breakdownCustomerFees' => $this->breakdownCustomerFees,
            'breakdownRSANavContribution' => $this->breakdownRSANavContribution,
            'breakdownRSAContribution' => $this->breakdownRSAContribution,
            'breakdownFollowUpNav' => $this->breakdownFollowUpNav,
            'breakdownInternalTransfer' => $this->breakdownInternalTransfer,
            'breakdownPenRefund' => $this->breakdownPenRefund,
            'breakdownMeeting' => $this->breakdownMeeting,
            'breakdownPaypalFees' => $this->breakdownPaypalFees,
            'breakdownSogecomFees' => $this->breakdownSogecomFees,
            'breakdownOsac' => $this->breakdownOsac,
            'breakdownOther' => $this->breakdownOther,
            'breakdownDonation' => $this->breakdownDonation,
            'breakdownVibrationDebit' => $this->breakdownVibrationDebit,
            'breakdownVibrationCredit' => $this->breakdownVibrationCredit,
            'description' => $this->description,
        ];
    }
}
