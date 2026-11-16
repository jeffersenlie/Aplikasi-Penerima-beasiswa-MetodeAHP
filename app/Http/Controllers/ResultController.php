<?php

namespace App\Http\Controllers;

use App\Services\ResultService;
use Illuminate\Http\Request;

class ResultController extends Controller
{
    public const DEFAULT_QUOTA = 2;

    public function index(Request $request, ResultService $results)
    {
        $quota = (int) $request->query('quota', self::DEFAULT_QUOTA);

        return view('results.index', ['result' => $results->compute($quota)]);
    }
}