<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\YearService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class HomeController extends Controller
{
    public function __construct(private YearService $yearService)
    {
    }

    public function home(): Response
    {
        return response(view('home', ['name' => 'John Doe']));
    }
}
