<?php

namespace App\Http\Controllers;

use App\Services\PairwiseService;

class WeightController extends Controller
{
    public function index(PairwiseService $pairwise)
    {
        return view('weights.index', $pairwise->hierarchy());
    }
}
