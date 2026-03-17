<?php

namespace App\Services;

use Doctrine\ORM\EntityManager;
use App\Domain\Line;
use App\Domain\LineBreakdown;

final class PaypalImportService
{
    public function __construct(
        private EntityManager $em
    ) {
    }

    public function import($handle): void
    {
        $this->em->getConnection()->beginTransaction();

        $fileLine = 1;

        while (($line = fgets($handle)) !== false) {
            if ($fileLine++ === 1) {
                $line = str_getcsv($line, ",");
                if (count($line) !== 41) {
                    throw new \Exception("Not a Paypal CSV file");
                }
                continue;
            }
            $line = str_getcsv($line, ",");
            $this->em->persist($this->createLine($line));
            $fees = $this->createFeesLine($line);
            if ($fees) {
                $this->em->persist($fees);
            }
        }

        $this->em->flush();
        $this->em->getConnection()->commit();
    }

    public function createLine(array $data): Line
    {
        $line = new Line();
        $timezone = new \DateTimeZone($data[2] ?? 'Europe/Paris');
        $line->setDate(\DateTimeImmutable::createFromFormat("d/m/Y H:i:s", $data[0] . ' ' . $data[1], $timezone));
        $line->setName($data[3]);
        $line->setType("PAYPAL");
        $line->setAmount($this->toFloat($data[7]));
        $line->setLabel($data[15]);
        if ($line->getAmount() >= 120) {
            // Supérieur à 120€, c'est un renouvellement d'avion
            $line->setBreakdown([LineBreakdown::PLANE_RENEWAL]);
            $line->breakdownPlaneRenewal = 120;
            $line->breakdownCustomerFees = $line->getAmount() - 120;
            if ($line->breakdownCustomerFees > 0) {
                $line->addBreakdown(LineBreakdown::CUSTOMER_FEES);
            }
        } elseif ($line->getAmount() > 0) {
            // Inférieur à 120€, c'est une contribution RSA
            $line->setBreakdown([LineBreakdown::RSA_NAV_CONTRIBUTION]);
            $line->breakdownRSANavContribution = $line->getAmount();
        } else {
            // Montant négatif, c'est un transfert vers la SG
            $line->setBreakdown([LineBreakdown::INTERNAL_TRANSFER]);
            $line->breakdownInternalTransfer = $line->getAmount();
        }
        $line->setDescription($data[26]);

        return $line;
    }

    public function createFeesLine(array $data): Line | null
    {
        $line = new Line();
        $timezone = new \DateTimeZone($data[2] ?? 'Europe/Paris');
        $line->setDate(\DateTimeImmutable::createFromFormat("d/m/Y H:i:s", $data[0] . ' ' . $data[1], $timezone));
        $line->setType("PAYPAL");
        $line->setName($data[3]);
        $line->setAmount($this->toFloat($data[8]));
        $line->setBreakdown([LineBreakdown::PAYPAL_FEES]);
        $line->breakdownPaypalFees = $line->getAmount();
        if ($line->getAmount() == 0) {
            return null;
        }
        return $line;
    }

    private function toFloat(string $value): float
    {
        return floatval(strtr($value, [',' => '.', ' ' => '', ' ' => '']));
    }
}
