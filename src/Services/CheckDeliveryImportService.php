<?php

namespace App\Services;

use Doctrine\ORM\EntityManager;
use App\Domain\CheckDelivery;
use App\Domain\CheckDeliveryLine;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

final class CheckDeliveryImportService
{
    public function __construct(
        private EntityManager $em
    ) {
    }

    public function import(Spreadsheet $spreadsheet): void
    {
        $this->em->getConnection()->beginTransaction();

        $fileLine = 1;

        $checkDelivery = new CheckDelivery();

        $worksheet = $spreadsheet->getActiveSheet();
        $date = NumberFormat::toFormattedString($worksheet->getCell('B15')->getValue(), 'YYYY-MM-DD');
        $checkDelivery->setDate(\DateTimeImmutable::createFromFormat("Y-m-d", $date));
        $this->em->persist($checkDelivery);
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
            $checkDeliveryLine->setCheckNumber($cells[0]);
            $checkDeliveryLine->setName($cells[1]);
            $checkDeliveryLine->setLabel($cells[2]);
            $checkDeliveryLine->setAmount(\is_string($cells[3]) ? $this->doMath($cells[3]) : $cells[3]);

            $this->em->persist($checkDeliveryLine);
            $checkDelivery->addLine($checkDeliveryLine);
            $checkDelivery->setAmount($checkDelivery->getAmount() + $checkDeliveryLine->getAmount());
            $rowIterator->next();
        }

        $this->em->flush();
        $this->em->getConnection()->commit();
    }

    private function doMath(string $value): float
    {
        $o = 0;
        eval('$o = ' . preg_replace('/[^0-9\+\-\*\/\(\)\.]/', '', $value) . ';');
        return $o;
    }
}
