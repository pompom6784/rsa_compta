<?php

namespace App\Services;

use App\Models\CheckDelivery;
use App\Models\CheckDeliveryLine;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

final class CheckDeliveryImportService
{
    public function import(Spreadsheet $spreadsheet): void
    {
        \DB::beginTransaction();

        $checkDelivery = new CheckDelivery();

        $worksheet = $spreadsheet->getActiveSheet();
        $date = NumberFormat::toFormattedString($worksheet->getCell('B15')->getValue(), 'YYYY-MM-DD');
        $checkDelivery->date = \DateTimeImmutable::createFromFormat("Y-m-d", $date);
        $checkDelivery->amount = 0;
        $checkDelivery->save();

        $rowIterator = $worksheet->getRowIterator();
        $rowIterator->seek(19);
        $i = 1;
        while ($i++ <= 30) {
            $checkDeliveryLine = new CheckDeliveryLine();
            $row = $rowIterator->current();
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false); // This loops through all cells,
            $cells = [];
            foreach ($cellIterator as $cell) {
                $cells[] = $cell->getValue();
            }
            if ($cells[0] === null) {
                break;
            }
            $checkDeliveryLine->check_number = $cells[0];
            $checkDeliveryLine->name = $cells[1];
            $checkDeliveryLine->label = $cells[2];
            $checkDeliveryLine->amount = \is_string($cells[3]) ? $this->doMath($cells[3]) : $cells[3];
            $checkDeliveryLine->check_delivery_id = $checkDelivery->id;
            $checkDeliveryLine->save();

            $checkDelivery->amount += $checkDeliveryLine->amount;
            $rowIterator->next();
        }

        $checkDelivery->save();

        \DB::commit();
    }

    private function doMath(string $value): float
    {
        $o = 0;
        eval('$o = ' . preg_replace('/[^0-9\+\-\*\/\(\)\.]/', '', $value) . ';');
        return $o;
    }
}
