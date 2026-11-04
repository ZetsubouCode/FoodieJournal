<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\Visit;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $base = Visit::where('user_id', $request->user()->id);

        return view('admin.dashboard', [
            'counts' => (clone $base)->selectRaw('status, COUNT(*) AS total')->groupBy('status')->pluck('total', 'status'),
            'drafts' => (clone $base)->where('status', 'draft')->with('place')->latest('updated_at')->limit(10)->get(),
            'recent' => (clone $base)->where('status', 'published')->with('place')->latest('visited_at')->limit(5)->get(),
            'failed' => Media::whereHas('visit', fn ($q) => $q->where('user_id', $request->user()->id))->where('processing_status', 'failed')->with('visit.place')->get(),
        ]);
    }
}
