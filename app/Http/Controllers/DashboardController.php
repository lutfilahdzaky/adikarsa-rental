<?php

namespace App\Http\Controllers;

use App\Models\Rental;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $totalRentals = Rental::count();
        $activeRentals = Rental::whereIn('status', ['Disetujui', 'Sedang Disewa'])->count();
        $completedRentals = Rental::where('status', 'Selesai')->count();
        $pendingRentals = Rental::where('status', 'Menunggu')->count();

        return Inertia::render('Dashboard', [
            'stats' => [
                'totalRentals' => $totalRentals,
                'activeRentals' => $activeRentals,
                'completedRentals' => $completedRentals,
                'pendingRentals' => $pendingRentals,
            ],
        ]);
    }
}
