<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class HomeController extends Controller
{
    public function __construct()
    {
    }

    public function home(): Response
    {
        return response(view('home', ['name' => 'John Doe']));
    }
}
