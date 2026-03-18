<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Line;
use App\Domain\LineBreakdown;
use App\Infrastructure\Persistence\CheckDelivery\DbCheckDeliveryRepository;
use App\Infrastructure\Persistence\Line\DbLineRepository;
use App\Services\ExcelExportService;
use Doctrine\ORM\QueryBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class BookController extends Controller
{
    public function __construct(
        private DbLineRepository $lineRepository,
        private DbCheckDeliveryRepository $checkDeliveryRepository,
        private ExcelExportService $excelExportService,
    ) {
    }

    public function index(): Response
    {
        return response(view('book'));
    }

    public function breakdown(): Response
    {
        return response(view('book', ['filter' => 'breakdown']));
    }

    public function lines(Request $request): JsonResponse
    {
        $params = $request->all();
        $qb = $this->buildQueryBuilder();

        // Map DataTables column keys to allowed Line entity fields
        $columnFieldMap = [
            'amount'    => 'amount',
            'date'      => 'date',
            'type'      => 'type',
            'label'     => 'label',
            'name'      => 'name',
            'breakdown' => 'breakdown',
        ];

        if (!empty($params['search']['value'])) {
            $qb->andWhere($qb->expr()->orX(
                'l.amount LIKE :search',
                'l.date LIKE :search',
                'l.type LIKE :search',
                'l.label LIKE :search',
                'l.name LIKE :search',
            ));
            $qb->setParameter('search', '%' . $params['search']['value'] . '%');
        }

        foreach (($params['columns'] ?? []) as $column) {
            if (empty($column['search']['value'])) {
                continue;
            }

            $columnKey = $column['data'] ?? null;

            // Skip unknown columns to avoid injecting unexpected DQL identifiers
            if ($columnKey === null) {
                continue;
            }

            match ($columnKey) {
                'credit', 'debit' => $qb->andWhere('l.amount LIKE :search_' . $columnKey),
                'date'            => $qb->andWhere('l.date LIKE :search_' . $columnKey),
                'breakdown'       => !empty($column['search']['value'])
                    ? $qb->andWhere('l.breakdown IS NOT NULL')
                    : $qb->andWhere('l.breakdown IS NULL'),
                default           => isset($columnFieldMap[$columnKey])
                    ? $qb->andWhere('l.' . $columnFieldMap[$columnKey] . ' LIKE :search_' . $columnKey)
                    : null,
            };

            if ($columnKey !== 'breakdown' && (isset($columnFieldMap[$columnKey]) || $columnKey === 'credit' || $columnKey === 'debit')) {
                $qb->setParameter('search_' . $columnKey, '%' . $column['search']['value'] . '%');
            }
        }

        $qbLines = clone $qb;
        if (!empty($params['order']) && isset($params['order'][0]['column'])) {
            $orderColumnIndex = (int) $params['order'][0]['column'];
            $sortKey          = $params['columns'][$orderColumnIndex]['data'] ?? null;
            $order            = $params['order'][0]['dir'] ?? 'asc';

            if ($sortKey !== null && isset($columnFieldMap[$sortKey])) {
                $qbLines->orderBy('l.' . $columnFieldMap[$sortKey], $order);
            }
        }

        $lines = $qbLines->getQuery()
            ->setFirstResult(!empty($params['start']) ? (int) $params['start'] : 0)
            ->setMaxResults(!empty($params['length']) ? (int) $params['length'] : 10)
            ->getResult();

        return response()->json([
            'draw'            => !empty($params['draw']) ? (int) $params['draw'] : 1,
            'recordsTotal'    => $this->buildQueryBuilder()->select('count(l.id)')->getQuery()->getSingleScalarResult(),
            'recordsFiltered' => $qb->select('count(l.id)')->getQuery()->getSingleScalarResult(),
            'data'            => $lines,
        ]);
    }

    public function lineEdit(Request $request, int $id): Response|RedirectResponse
    {
        $line = $this->lineRepository->findLineOfId($id);
        if (!$line) {
            return redirect()->route('book');
        }

        if ($request->input('check_delivery')) {
            return $this->convertCheckDelivery($request, $line);
        }

        if ($request->isMethod('post')) {
            $line->setType((string) $request->input('type'));
            $line->setLabel((string) $request->input('label'));
            $line->setName((string) $request->input('name'));
            $line->setBreakdown((array) $request->input('breakdown', []));
            foreach (array_keys(LineBreakdown::getBreakdowns()) as $breakdown) {
                $key = 'breakdown' . $breakdown;
                $line->__set($key, self::parseCurrency($request->input($key)));
            }
            $this->lineRepository->save($line);

            return redirect()->route('book');
        }

        $vars = [
            'line'       => $line,
            'breakdowns' => LineBreakdown::getBreakdowns(),
        ];

        if ($line->getLabel() === 'REMISES DE CHEQUES') {
            $checkCount = 0;
            if (preg_match('/DE\s+([0-9]+)\s+CHQ/', (string) $line->getDescription(), $matches)) {
                $checkCount = (int) $matches[1];
            }
            $vars['check_count']      = $checkCount;
            $vars['check_deliveries'] = $this->checkDeliveryRepository
                ->findByDifference($line->getAmount(), $checkCount, $line->getDate());
        }

        return response(view('edit_line', $vars));
    }

    public function excel(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        return $this->excelExportService->export();
    }

    private function convertCheckDelivery(Request $request, Line $line): RedirectResponse
    {
        $checkDelivery = $this->checkDeliveryRepository
            ->findCheckDeliveryOfId((int) $request->input('check_delivery'));

        foreach ($checkDelivery->getLines() as $checkDeliveryLine) {
            $newLine = new Line();
            $newLine->setType('CHQ');
            $newLine->setName((string) $checkDeliveryLine->getName());
            $newLine->setLabel((string) $checkDeliveryLine->getLabel());
            $newLine->setDescription(
                'Chèque n°' . $checkDeliveryLine->getCheckNumber()
                . ' remis le ' . $checkDelivery->getDate()->format('d/m/Y')
            );
            $newLine->setDate($checkDelivery->getDate());
            $newLine->setAmount($checkDeliveryLine->getAmount());

            if (str_starts_with((string) $checkDeliveryLine->getLabel(), 'COTISATION')) {
                $newLine->setBreakdown([LineBreakdown::RSA_NAV_CONTRIBUTION]);
                $newLine->breakdownInternalTransfer = $newLine->getAmount();
            } else {
                $newLine->setBreakdown([LineBreakdown::PLANE_RENEWAL]);
                $newLine->breakdownPlaneRenewal  = 120;
                $newLine->breakdownCustomerFees  = $newLine->getAmount() - 120;
                if ($newLine->breakdownCustomerFees > 0) {
                    $newLine->addBreakdown(LineBreakdown::CUSTOMER_FEES);
                }
            }
            $this->lineRepository->save($newLine);
        }

        $this->lineRepository->delete($line);
        $checkDelivery->setConverted(true);
        $this->checkDeliveryRepository->save($checkDelivery);

        return redirect()->route('book');
    }

    private function buildQueryBuilder(): QueryBuilder
    {
        $ignoredBreakdowns = [
            LineBreakdown::PAYPAL_FEES,
            LineBreakdown::SOGECOM_FEES,
            LineBreakdown::INTERNAL_TRANSFER,
        ];
        $qb = $this->lineRepository->getQueryBuilder();
        $qb->where('l.breakdown IS NULL OR l.breakdown NOT IN (:breakdown)');
        $qb->setParameter('breakdown', $ignoredBreakdowns);

        return $qb;
    }

    private static function parseCurrency(?string $currency): float
    {
        if (empty($currency)) {
            return 0.0;
        }

        return (float) str_replace(',', '.', (string) preg_replace('/[^-0-9,]/', '', $currency));
    }
}
