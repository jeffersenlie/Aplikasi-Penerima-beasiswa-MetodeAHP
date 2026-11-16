<?php

namespace App\Http\Controllers;

use App\Models\Calculation;
use Illuminate\Http\Request;

class HistoryController extends Controller
{
    public function index(Request $request)
    {
        $calculations = Calculation::where('user_id', $request->user()->id)
            ->with(['results' => fn ($q) => $q->where('eligible', true)])
            ->latest()
            ->get();

        return view('history.index', compact('calculations'));
    }

    public function show(Request $request, Calculation $calculation)
    {
        abort_unless($calculation->user_id === $request->user()->id, 403);

        $calculation->load('results');

        return view('history.show', compact('calculation'));
    }
}