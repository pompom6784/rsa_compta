<?php

namespace App\Services;

use App\Models\Line;
use App\Models\LineBreakdown;

final class SGImportService
{
    public function import($handle): void
    {
        \DB::beginTransaction();

        $lineNumber = 1;

        $fileLine = fgets($handle);
        while ($fileLine !== false) {
            if ($lineNumber++ <= 7) {
                if ($lineNumber === 3) {
                    $data = str_getcsv($fileLine, ";");
                    if ($data[0] !== "FR76 3000 3031 2200 0501 3271 922") {
                        throw new \Exception("Not a SG CSV file");
                    }
                }
                $fileLine = fgets($handle);
                continue;
            }
            $data = str_getcsv($fileLine, ";");
            try {
                $this->createLine($data, $handle, $fileLine);
            } catch (\Exception $e) {
                error_log("Error processing line $lineNumber: " . $e->getMessage());
                $fileLine = fgets($handle);
            }
        }

        \DB::commit();
    }

    public function createLine(array $data, $handle, &$fileLine): void
    {
        $line = new Line();
        $timezone = new \DateTimeZone('Europe/Paris');
        $date = \DateTimeImmutable::createFromFormat("d/m/Y H:i:s", $data[5] . "01:00:00", $timezone);
        if (!$date) {
            $fileLine = fgets($handle);
            return;
        }
        $line->date = $date;
        $line->amount = $this->toFloat($data[2] == "" ? $data[3] : $data[2]);
        $line->label = $data[6];

        // Capture the first raw CSV line before reading continuation lines
        $rawLines = [$fileLine];

        $description = $data[1] . "\n";

        $fileLine = fgets($handle);
        $data = str_getcsv($fileLine, ";");
        while ($data[0] == '' && !empty($data[1])) {
            $rawLines[] = $fileLine;
            $description .= $data[1] . "\n";
            $fileLine = fgets($handle);
            $data = str_getcsv($fileLine, ";");
        }

        $line->description = $description;
        $line->import_hash = $this->computeHash($rawLines);

        // Skip if a line with the same raw-data hash was already imported
        if (Line::where('import_hash', $line->import_hash)->exists()) {
            return;
        }

        $line = $this->qualifyLine($line);

        if ($line) {
            $line->save();
        }
    }

    /**
     * Compute a SHA-256 hash over all raw CSV lines that make up one logical
     * SG entry (the primary line plus any continuation lines).
     *
     * @param string[] $rawLines
     */
    private function computeHash(array $rawLines): string
    {
        return hash('sha256', implode('', $rawLines));
    }

    private function qualifyLine(Line $line): Line | null
    {
        if (strpos($line->description, 'ABONNT ENCAISSEMENT INTERNET') === 0) {
            $line->type = 'VRT';
            $line->breakdown = [LineBreakdown::SOGECOM_FEES];
            $line->breakdown_sogecom_fees = $line->amount;
            return $line;
        }
        if (strpos($line->description, 'COTISATION JAZZ ASSOCIATIONS') === 0) {
            $line->type = 'VRT';
            $line->breakdown = [LineBreakdown::INTERNAL_TRANSFER];
            $line->breakdown_internal_transfer = $line->amount;
            return $line;
        }
        // Frais déjà comptabilisés dans l'import Sogecom
        if (strpos($line->description, 'REMISE CB') === 0) {
            if (
                $line->label === 'FACTURES CARTES REMISES'
                || $line->label === 'COMMISSIONS ET FRAIS DIVERS'
            ) {
                return null;
            }
        }
        if ($line->label === 'AUTRES VIREMENTS RECUS' || strpos($line->description, 'VIR INST RE') === 0) {
            $line->type = 'VRT';
        }
        if (strpos($line->description, 'DE: PayPal Europe S.a.r.l. et Cie S.C.A') !== false) {
            $line->breakdown = [LineBreakdown::INTERNAL_TRANSFER];
            $line->breakdown_internal_transfer = $line->amount;
            return $line;
        }
        if ($line->label === 'AUTRES VIREMENTS EMIS') {
            $line->type = 'VRT';
            if (
                (
                    strpos($line->description, 'RBTS FRAIS PEN') !== false
                    || strpos($line->description, 'RBT FRAIS PEN') !== false
                )
                && $line->amount < 0
            ) {
                $line->breakdown = [LineBreakdown::PEN_REFUND];
                $line->breakdown_pen_refund = $line->amount;
                $line->name = 'PEN';
                return $line;
            }
            if (strpos($line->description, 'OSAC  DFFAI') !== false && $line->amount < 0) {
                $line->breakdown = [LineBreakdown::OSAC];
                $line->breakdown_osac = $line->amount;
                $line->name = 'OSAC';
                return $line;
            }
        }
        return $line;
    }

    private function toFloat(string $value): float
    {
        return floatval(strtr(str_replace(' EUR', '', $value), [',' => '.', ' ' => '', "\xc2\xa0" => '']));
    }
}
