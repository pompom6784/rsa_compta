<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\CheckDelivery;
use App\Models\Line;
use App\Models\LineBreakdown;
use App\Services\ExcelExportService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class BookController extends Controller
{
    public function __construct(
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
            $search = $params['search']['value'];
            $qb->where(function ($q) use ($search) {
                $q->where('amount', 'like', '%' . $search . '%')
                  ->orWhere('date', 'like', '%' . $search . '%')
                  ->orWhere('type', 'like', '%' . $search . '%')
                  ->orWhere('label', 'like', '%' . $search . '%')
                  ->orWhere('name', 'like', '%' . $search . '%');
            });
        }

        foreach (($params['columns'] ?? []) as $column) {
            $columnKey = $column['data'] ?? null;

            // Skip unknown columns to avoid injecting unexpected identifiers
            if ($columnKey === null) {
                continue;
            }

            $searchVal = $column['search']['value'];

            match ($columnKey) {
                'credit', 'debit' => $qb->where('amount', 'like', '%' . $searchVal . '%'),
                'date'            => $qb->where('date', 'like', '%' . $searchVal . '%'),
                'breakdown'       => !(empty($searchVal) ? $qb->whereNotNull('breakdown') : $qb->whereNull('breakdown')),
                default           => isset($columnFieldMap[$columnKey])
                    ? $qb->where($columnFieldMap[$columnKey], 'like', '%' . $searchVal . '%')
                    : null,
            };
        }

        $qbLines = clone $qb;
        if (!empty($params['order']) && isset($params['order'][0]['column'])) {
            $orderColumnIndex = (int) $params['order'][0]['column'];
            $sortKey          = $params['columns'][$orderColumnIndex]['data'] ?? null;
            $orderDir         = strtoupper($params['order'][0]['dir'] ?? 'ASC');
            $order            = in_array($orderDir, ['ASC', 'DESC'], true) ? $orderDir : 'ASC';

            if ($sortKey !== null && isset($columnFieldMap[$sortKey])) {
                $qbLines->orderBy($columnFieldMap[$sortKey], $order);
            }
        }

        $lines = $qbLines
            ->skip(!empty($params['start']) ? (int) $params['start'] : 0)
            ->take(!empty($params['length']) ? (int) $params['length'] : 10)
            ->get();

        return response()->json([
            'draw'            => !empty($params['draw']) ? (int) $params['draw'] : 1,
            'recordsTotal'    => $this->buildQueryBuilder()->count(),
            'recordsFiltered' => $qb->count(),
            'data'            => $lines,
        ]);
    }

    public function lineEdit(Request $request, int $id): Response|RedirectResponse
    {
        $line = Line::find($id);
        if (!$line) {
            return redirect()->route('book');
        }

        if ($request->input('check_delivery')) {
            return $this->convertCheckDelivery($request, $line);
        }

        $columnKeys = LineBreakdown::columnKeys();

        if ($request->isMethod('post')) {
            $line->type = (string) $request->input('type');
            $line->label = (string) $request->input('label');
            $line->name = (string) $request->input('name');
            $line->breakdown = array_values((array) $request->input('breakdown', []));
            foreach (array_keys(LineBreakdown::getBreakdowns()) as $breakdown) {
                $inputKey  = 'breakdown' . $breakdown;
                $columnKey = $columnKeys[$breakdown] ?? null;
                if ($columnKey !== null) {
                    $line->{$columnKey} = self::parseCurrency($request->input($inputKey));
                }
            }
            $line->save();

            return redirect()->route('book');
        }

        $vars = [
            'line'       => $line,
            'breakdowns' => LineBreakdown::getBreakdowns(),
            'columnKeys' => $columnKeys,
        ];

        if ($line->label === 'REMISES DE CHEQUES') {
            $checkCount = 0;
            if (preg_match('/DE\s+([0-9]+)\s+CHQ/', (string) $line->description, $matches)) {
                $checkCount = (int) $matches[1];
            }
            $vars['check_count']      = $checkCount;
            $vars['check_deliveries'] = $this->findByDifference($line->amount, $checkCount, $line->date);
        }

        return response(view('edit_line', $vars));
    }

    public function excel(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        return $this->excelExportService->export();
    }

    private function convertCheckDelivery(Request $request, Line $line): RedirectResponse
    {
        $checkDelivery = CheckDelivery::find((int) $request->input('check_delivery'));

        if (!$checkDelivery) {
            return redirect()->route('book');
        }

        foreach ($checkDelivery->lines as $checkDeliveryLine) {
            $newLine = new Line();
            $newLine->type = 'CHQ';
            $newLine->name = (string) $checkDeliveryLine->name;
            $newLine->label = (string) $checkDeliveryLine->label;
            $newLine->description =
                'Chèque n°' . $checkDeliveryLine->check_number
                . ' remis le ' . $checkDelivery->date->format('d/m/Y');
            $newLine->date = $checkDelivery->date;
            $newLine->amount = $checkDeliveryLine->amount;

            if (str_starts_with((string) $checkDeliveryLine->label, 'COTISATION')) {
                $newLine->breakdown = [LineBreakdown::RSA_NAV_CONTRIBUTION];
                $newLine->breakdown_rsa_nav_contribution = $newLine->amount;
            } else {
                $newLine->breakdown = [LineBreakdown::PLANE_RENEWAL];
                $newLine->breakdown_plane_renewal  = 120;
                $newLine->breakdown_customer_fees  = $newLine->amount - 120;
                if ($newLine->breakdown_customer_fees > 0) {
                    $newLine->breakdown = array_merge($newLine->breakdown ?? [], [LineBreakdown::CUSTOMER_FEES]);
                }
            }
            $newLine->save();
        }

        $line->delete();
        $checkDelivery->converted = true;
        $checkDelivery->save();

        return redirect()->route('book');
    }

    private function buildQueryBuilder(): Builder
    {
        $ignoredBreakdowns = [
            LineBreakdown::PAYPAL_FEES,
            LineBreakdown::SOGECOM_FEES,
            LineBreakdown::INTERNAL_TRANSFER,
        ];

        return Line::query()->where(function ($q) use ($ignoredBreakdowns) {
            $q->whereNull('breakdown');
            $q->orWhere(function ($q2) use ($ignoredBreakdowns) {
                foreach ($ignoredBreakdowns as $breakdown) {
                    $q2->where('breakdown', 'not like', '%"' . $breakdown . '"%');
                }
            });
        });
    }

    private function findByDifference(float $amount, int $count, $date): array
    {
        $dateStr = $date instanceof \DateTimeInterface ? $date->format('Y-m-d') : (string) $date;

        return CheckDelivery::query()
            ->leftJoin('check_deliveries_line', 'check_deliveries.id', '=', 'check_deliveries_line.check_delivery_id')
            ->where('check_deliveries.converted', false)
            ->groupBy('check_deliveries.id')
            ->select('check_deliveries.*')
            ->orderByRaw('ABS(check_deliveries.amount - ?)', [$amount])
            ->orderByRaw('ABS(COUNT(check_deliveries_line.id) - ?)', [$count])
            ->orderByRaw('ABS(julianday(check_deliveries.date) - julianday(?))', [$dateStr])
            ->get()
            ->all();
    }

    private static function parseCurrency(?string $currency): float
    {
        if (empty($currency)) {
            return 0.0;
        }

        return (float) str_replace(',', '.', (string) preg_replace('/[^-0-9,]/', '', $currency));
    }
}
