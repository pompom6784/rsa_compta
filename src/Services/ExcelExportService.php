<?php

namespace App\Services;

use App\Domain\Line;
use App\Domain\LineBreakdown;
use App\Infrastructure\Persistence\Line\DbLineRepository;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

final class ExcelExportService
{
    public function __construct(
        protected DbLineRepository $lineRepository,
    ) {
    }

    protected Spreadsheet $spreadsheet;
    protected Worksheet $activeWorksheet;

    protected int $currentLine = 2;
    protected int $from = 0;
    protected int $step = 50;

    public function export()
    {
        @$this->spreadsheet = IOFactory::load('/var/www/public/template GRAND LIVRE.xlsx');

        $this->activeWorksheet = $this->spreadsheet->getActiveSheet();

        $this->insertLines();

        $this->insertFeeLines();

        $this->insertSums();

        $writer = new Xlsx($this->spreadsheet);
        $writer->save('export.xlsx');
    }

    protected function loadLines(): array
    {
        $breakdowns = [LineBreakdown::PAYPAL_FEES, LineBreakdown::SOGECOM_FEES, LineBreakdown::INTERNAL_TRANSFER];
        $qb = $this->lineRepository->getQueryBuilder()
          ->where('l.breakdown IS NULL OR l.breakdown NOT IN (:breakdown)')
          ->setParameter('breakdown', $breakdowns)
          ->orderBy('l.date', 'ASC')
          ->setFirstResult($this->from)
          ->setMaxResults($this->step);

        return $qb->getQuery()->getResult();
    }

    protected function insertLines()
    {
        while ($lines = $this->loadLines()) {
            foreach ($lines as $line) {
                $this->insertLine($line);
                $this->currentLine++;
            }
            $this->from += $this->step;
        }
    }

    protected function insertFeeLines()
    {
        $sogecomFees = $this->lineRepository->getQueryBuilder()
        ->select('SUM(l.breakdownSogecomFees)')
        ->getQuery()->getSingleScalarResult();
        $this->activeWorksheet->setCellValue('A' . $this->currentLine, "Sogecom");
        $this->activeWorksheet->setCellValue('B' . $this->currentLine, "31/12/2023");
        $this->activeWorksheet->setCellValue('C' . $this->currentLine, "Sogecom");
        $this->activeWorksheet->setCellValue('D' . $this->currentLine, "Frais annuels transactions Sogecom");
        $this->activeWorksheet->setCellValue('E' . $this->currentLine, self::formatCurrency($sogecomFees));
        $this->activeWorksheet
        ->setCellValue('O' . $this->currentLine, self::formatCurrency($sogecomFees));
        $this->currentLine += 1;
        $paypalFees = $this->lineRepository->getQueryBuilder()
        ->select('SUM(l.breakdownPaypalFees)')
        ->getQuery()->getSingleScalarResult();
        $this->activeWorksheet->setCellValue('A' . $this->currentLine, "PAYPAL");
        $this->activeWorksheet->setCellValue('B' . $this->currentLine, "31/12/2023");
        $this->activeWorksheet->setCellValue('C' . $this->currentLine, "PAYPAL");
        $this->activeWorksheet->setCellValue('D' . $this->currentLine, "Frais annuels transactions Paypal");
        $this->activeWorksheet->setCellValue('E' . $this->currentLine, self::formatCurrency($paypalFees));
        $this->activeWorksheet
        ->setCellValue('N' . $this->currentLine, self::formatCurrency($paypalFees));
        $this->currentLine += 1;
    }

    protected function insertSums()
    {
        $sheet = $this->activeWorksheet;
        $this->currentLine += 3;
        $sheet->setCellValue('D' . $this->currentLine, 'Encaissements/Décaissements');
        $sheet->setCellValue('E' . $this->currentLine, '=SUM(E2:E' . ($this->currentLine - 1) . ')');
        $sheet->setCellValue('F' . $this->currentLine, '=SUM(F2:F' . ($this->currentLine - 1) . ')');
        $sheet->setCellValue('G' . $this->currentLine, '=SUM(G2:G' . ($this->currentLine - 1) . ')');
        $sheet->setCellValue('H' . $this->currentLine, '=SUM(H2:H' . ($this->currentLine - 1) . ')');
        $sheet->setCellValue('I' . $this->currentLine, '=SUM(I2:I' . ($this->currentLine - 1) . ')');
        $sheet->setCellValue('J' . $this->currentLine, '=SUM(J2:J' . ($this->currentLine - 1) . ')');
        $sheet->setCellValue('K' . $this->currentLine, '=SUM(K2:K' . ($this->currentLine - 1) . ')');
        $sheet->setCellValue('L' . $this->currentLine, '=SUM(L2:L' . ($this->currentLine - 1) . ')');
        $sheet->setCellValue('M' . $this->currentLine, '=SUM(M2:M' . ($this->currentLine - 1) . ')');
        $sheet->setCellValue('N' . $this->currentLine, '=SUM(N2:N' . ($this->currentLine - 1) . ')');
        $sheet->setCellValue('O' . $this->currentLine, '=SUM(O2:O' . ($this->currentLine - 1) . ')');
        $sheet->setCellValue('P' . $this->currentLine, '=SUM(P2:P' . ($this->currentLine - 1) . ')');
        $sheet->setCellValue('Q' . $this->currentLine, '=SUM(Q2:Q' . ($this->currentLine - 1) . ')');
        $sheet->setCellValue('R' . $this->currentLine, '=SUM(R2:R' . ($this->currentLine - 1) . ')');
        $sheet->setCellValue('S' . $this->currentLine, '=SUM(S2:S' . ($this->currentLine - 1) . ')');
        $sheet->setCellValue('T' . $this->currentLine, '=SUM(T2:T' . ($this->currentLine - 1) . ')');
        $sumsLine = $this->currentLine;
        $sheet->getStyle('D' . $this->currentLine . ':T' . $this->currentLine)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFECF1DF');
        $this->currentLine++;
        $sheet->setCellValue('D' . $this->currentLine, 'CA Brut');
        $sheet->setCellValue(
            'F' . $this->currentLine,
            '=G' . $sumsLine . '+H' . $sumsLine . '+K' . $sumsLine . '+T' . $sumsLine
        );
        $sheet->setCellValue(
            'G' . $this->currentLine,
            'Recettes totales hors cotisations'
        );
        $sheet->getStyle('G' . $this->currentLine)
            ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $sheet->setCellValue('I' . $this->currentLine, '=I' . $sumsLine . '/50');
        $sheet->setCellValue('J' . $this->currentLine, 'Nb mb RSANav calculé');
        $sheet->getStyle('J' . $this->currentLine)
            ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $this->currentLine++;
        $sheet->setCellValue('D' . $this->currentLine, 'Cotisations encaissées et dons');
        $sheet->setCellValue('F' . $this->currentLine, '=I' . $sumsLine . '+J' . $sumsLine . '+R' . $sumsLine);
        $sheet->getStyle('D' . ($this->currentLine - 1) . ':F' . $this->currentLine)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFDEE5F0');
        $this->currentLine++;
        $sheet->setCellValue('D' . $this->currentLine, 'Charges Brutes');
        $sheet->setCellValue(
            'F' . $this->currentLine,
            '=M' . $sumsLine . '+N' . $sumsLine . '+O' . $sumsLine . '+P' . $sumsLine . '+Q' . $sumsLine
        );
        $sheet->setCellValue('G' . $this->currentLine, 'Y compris rbt frais des PEN');
        $sheet->getStyle('G' . $this->currentLine)
            ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('D' . $this->currentLine . ':F' . $this->currentLine)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFBCCBE1');
        $this->currentLine++;
        $sheet->setCellValue('D' . $this->currentLine, 'RESULTAT BRUT');
        $sheet->setCellValue(
            'F' . $this->currentLine,
            '=F' . ($this->currentLine - 1) . '+F' . ($this->currentLine - 2) . '+F' . ($this->currentLine - 3)
        );
        $sheet->getStyle('D' . $this->currentLine . ':F' . $this->currentLine)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF92FCCF');
        $this->currentLine++;
        $sheet->setCellValue('D' . $this->currentLine, 'CA Net');
        $sheet->setCellValue('F' . $this->currentLine, '=G' . $sumsLine . '/2+K' . $sumsLine . '+T' . $sumsLine);
        $this->currentLine++;
        $sheet->setCellValue('D' . $this->currentLine, 'Cotisations nettes');
        $sheet->setCellValue('F' . $this->currentLine, '=I' . $sumsLine . '+J' . $sumsLine . '+R' . $sumsLine);
        $sheet->getStyle('D' . ($this->currentLine - 1) . ':F' . $this->currentLine)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFBFDDE7');
        $this->currentLine++;
        $sheet->setCellValue('D' . $this->currentLine, 'Charges Nettes');
        $sheet->setCellValue(
            'F' . $this->currentLine,
            '=(L' . $sumsLine . '+H' . $sumsLine . '+G' . $sumsLine
            . '/2)+M' . $sumsLine . '+N' . $sumsLine . '+O' . $sumsLine . '+P' . $sumsLine
            . '+Q' . $sumsLine . '+R' . $sumsLine
        );
        $sheet->getStyle('D' . $this->currentLine . ':F' . $this->currentLine)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF9FCBDA');
        $this->currentLine++;
        $sheet->setCellValue('D' . $this->currentLine, 'RESULTAT NET');
        $sheet->getStyle('D' . $sumsLine . ':F' . $this->currentLine)
            ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('D' . ($sumsLine + 1) . ':F' . $this->currentLine)
            ->getFont()->setBold(true);
        $sheet->setCellValue(
            'F' . $this->currentLine,
            '=F' . ($this->currentLine - 1) . '+F' . ($this->currentLine - 2) . '+F' . ($this->currentLine - 3)
        );
        $sheet->getStyle('D' . $this->currentLine . ':F' . $this->currentLine)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF92FCA2');
    }

    protected function insertLine(Line $line)
    {
        $this->activeWorksheet->setCellValue('A' . $this->currentLine, $line->getType());
        $this->activeWorksheet->setCellValue('B' . $this->currentLine, $line->getDate()->format('d/m/Y'));
        $this->activeWorksheet->setCellValue('C' . $this->currentLine, $line->getName());
        $this->activeWorksheet->setCellValue('D' . $this->currentLine, $line->getLabel());
        $this->activeWorksheet->setCellValue('E' . $this->currentLine, self::formatCurrency($line->getDebit()));
        $this->activeWorksheet->setCellValue('F' . $this->currentLine, self::formatCurrency($line->getCredit()));
        $this->activeWorksheet
            ->setCellValue('G' . $this->currentLine, self::formatCurrency($line->breakdownPlaneRenewal));
        $this->activeWorksheet
            ->setCellValue('H' . $this->currentLine, self::formatCurrency($line->breakdownCustomerFees));
        $this->activeWorksheet
            ->setCellValue('I' . $this->currentLine, self::formatCurrency($line->breakdownRSANavContribution));
        $this->activeWorksheet
            ->setCellValue('J' . $this->currentLine, self::formatCurrency($line->breakdownRSAContribution));
        $this->activeWorksheet
            ->setCellValue('K' . $this->currentLine, self::formatCurrency($line->breakdownFollowUpNav));
        $this->activeWorksheet
            ->setCellValue('L' . $this->currentLine, self::formatCurrency($line->breakdownPenRefund));
        $this->activeWorksheet
            ->setCellValue('M' . $this->currentLine, self::formatCurrency($line->breakdownMeeting));
        $this->activeWorksheet
            ->setCellValue('N' . $this->currentLine, self::formatCurrency($line->breakdownPaypalFees));
        $this->activeWorksheet
            ->setCellValue('O' . $this->currentLine, self::formatCurrency($line->breakdownSogecomFees));
        $this->activeWorksheet
            ->setCellValue('P' . $this->currentLine, self::formatCurrency($line->breakdownOsac));
        $this->activeWorksheet
            ->setCellValue('Q' . $this->currentLine, self::formatCurrency($line->breakdownOther));
        $this->activeWorksheet
            ->setCellValue('R' . $this->currentLine, self::formatCurrency($line->breakdownDonation));
        $this->activeWorksheet
            ->setCellValue('S' . $this->currentLine, self::formatCurrency($line->breakdownVibrationDebit));
        $this->activeWorksheet
            ->setCellValue('T' . $this->currentLine, self::formatCurrency($line->breakdownVibrationCredit));
    }

    protected static function formatCurrency($value)
    {
        if (empty($value)) {
            return '';
        }
        return $value;
    }
}
