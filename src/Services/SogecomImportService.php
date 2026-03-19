<?php

namespace App\Services;

use App\Models\Line;
use App\Models\LineBreakdown;

final class SogecomImportService
{
    public function import($handle): void
    {
        \DB::beginTransaction();

        $firstLine = true;

        while (($line = fgets($handle)) !== false) {
            if ($firstLine) {
                $firstLine = false;
                continue;
            }
            $line = mb_convert_encoding($line, 'ISO-8859-1', 'UTF-8');
            $data = str_getcsv($line, ";");
            $this->createLine($data)->save();
            $fees = $this->createFeesLine($data);
            if ($fees) {
                $fees->save();
            }
        }

        \DB::commit();
    }

    public function createLine(array $data): Line
    {
        $line = new Line();
        $timezone = new \DateTimeZone('Europe/Paris');
        $line->date = \DateTimeImmutable::createFromFormat("d/m/Y H:i:s", $data[0], $timezone)
            ?: throw new \RuntimeException('Invalid date: ' . $data[0]);
        $line->name = mb_convert_encoding($data[3], 'UTF-8', 'UTF-8');
        $line->type = "Sogecom";
        $line->amount = $this->toFloat($data[1]);
        if ($line->amount >= 120) {
            // Supérieur à 120€, c'est un renouvellement CDN
            $line->breakdown = [LineBreakdown::PLANE_RENEWAL];
            $line->breakdown_plane_renewal = 120;
            $line->breakdown_customer_fees = $line->amount - 120;
            if ($line->breakdown_customer_fees > 0) {
                $line->breakdown = array_merge($line->breakdown ?? [], [LineBreakdown::CUSTOMER_FEES]);
            }
            $line->label = 'Renouvellement CDN';
        } elseif ($line->amount > 0) {
            // Inférieur à 120€, c'est une contribution RSA
            $line->breakdown = [LineBreakdown::RSA_NAV_CONTRIBUTION];
            $line->breakdown_rsa_nav_contribution = $line->amount;
            $line->label = 'COTISATION RSA NAV ' . $line->date->format('Y');
        } else {
            // Montant négatif, c'est un transfert vers la SG
            $line->breakdown = [LineBreakdown::INTERNAL_TRANSFER];
            $line->breakdown_internal_transfer = $line->amount;
            $line->label = 'Virement vers la SG';
        }
        $line->description = mb_convert_encoding($data[4] . "\n" . $data[5], 'UTF-8', 'UTF-8');

        return $line;
    }

    public function createFeesLine(array $data): Line | null
    {
        $line = new Line();
        $timezone = new \DateTimeZone('Europe/Paris');
        $line->date = \DateTimeImmutable::createFromFormat("d/m/Y H:i:s", $data[0], $timezone)
            ?: throw new \RuntimeException('Invalid date: ' . $data[0]);
        $line->type = "Sogecom";
        $line->name = $data[3];
        $line->amount = $this->toFloat($data[6]) * -1;
        $line->breakdown = [LineBreakdown::SOGECOM_FEES];
        $line->breakdown_sogecom_fees = $line->amount;
        if ($line->amount == 0) {
            return null;
        }
        return $line;
    }

    private function toFloat(string $value): float
    {
        return floatval(strtr(str_replace(' EUR', '', $value), [',' => '.', ' ' => '', "\xc2\xa0" => '']));
    }
}
