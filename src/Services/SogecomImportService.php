<?php

namespace App\Services;

use Doctrine\ORM\EntityManager;
use App\Domain\Line;
use App\Domain\LineBreakdown;

final class SogecomImportService
{
    public function __construct(
        private EntityManager $em
    ) {
    }

    public function import($handle): void
    {
        $this->em->getConnection()->beginTransaction();

        $firstLine = true;

        while (($line = fgets($handle)) !== false) {
            if ($firstLine) {
                $firstLine = false;
                continue;
            }
            $line = mb_convert_encoding($line, 'ISO-8859-1', 'UTF-8');
            $data = str_getcsv($line, ";");
            $this->em->persist($this->createLine($data));
            $fees = $this->createFeesLine($data);
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
        $timezone = new \DateTimeZone('Europe/Paris');
        $line->setDate(\DateTimeImmutable::createFromFormat("d/m/Y H:i:s", $data[0], $timezone));
        $line->setName(mb_convert_encoding($data[3], 'UTF-8', 'UTF-8'));
        $line->setType("Sogecom");
        $line->setAmount($this->toFloat($data[1]));
        if ($line->getAmount() >= 120) {
            // Supérieur à 120€, c'est un renouvellement CDN
            $line->setBreakdown([LineBreakdown::PLANE_RENEWAL]);
            $line->breakdownPlaneRenewal = 120;
            $line->breakdownCustomerFees = $line->getAmount() - 120;
            if ($line->breakdownCustomerFees > 0) {
                $line->addBreakdown(LineBreakdown::CUSTOMER_FEES);
            }
            $line->setLabel('Renouvellement CDN');
        } elseif ($line->getAmount() > 0) {
            // Inférieur à 120€, c'est une contribution RSA
            $line->setBreakdown([LineBreakdown::RSA_NAV_CONTRIBUTION]);
            $line->breakdownRSANavContribution = $line->getAmount();
            $line->setLabel('COTISATION RSA NAV ' . $line->getDate()->format('Y'));
        } else {
            // Montant négatif, c'est un transfert vers la SG
            $line->setBreakdown([LineBreakdown::INTERNAL_TRANSFER]);
            $line->breakdownInternalTransfer = $line->getAmount();
            $line->setLabel('Virement vers la SG');
        }
        $line->setDescription(mb_convert_encoding($data[4] . "\n" . $data[5], 'UTF-8', 'UTF-8'));

        return $line;
    }

    public function createFeesLine(array $data): Line | null
    {
        $line = new Line();
        $timezone = new \DateTimeZone('Europe/Paris');
        $line->setDate(\DateTimeImmutable::createFromFormat("d/m/Y H:i:s", $data[0], $timezone));
        $line->setType("Sogecom");
        $line->setName($data[3]);
        $line->setAmount($this->toFloat($data[6]) * -1);
        $line->setBreakdown([LineBreakdown::SOGECOM_FEES]);
        $line->breakdownSogecomFees = $line->getAmount();
        if ($line->getAmount() == 0) {
            return null;
        }
        return $line;
    }

    private function toFloat(string $value): float
    {
        return floatval(strtr(str_replace(' EUR', '', $value), [',' => '.', ' ' => '', ' ' => '']));
    }
}
