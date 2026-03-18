<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\YearService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class YearController extends Controller
{
    public function __construct(private YearService $yearService)
    {
    }

    public function selectYear(): View
    {
        return view('select_year', [
            'years' => $this->yearService->getAvailableYears(),
            'current_year' => $this->yearService->getCurrentYear(),
        ]);
    }

    public function pickYear(Request $request): RedirectResponse
    {
        $year = $request->input('year');

        if ($year && $this->yearService->yearExists((string) $year)) {
            $this->yearService->setCurrentYear((string) $year);
        }

        return redirect('/');
    }
}
