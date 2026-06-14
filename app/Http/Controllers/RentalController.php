<?php

namespace App\Http\Controllers;

use App\Models\HeavyEquipment;
use App\Models\Rental;
use App\Models\RentalDetail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Carbon;
use Inertia\Inertia;

class RentalController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'administrator') {
            $rentals = Rental::with(['user', 'rentalDetails.heavyEquipment'])->orderByDesc('created_at')->get();
        } else {
            $rentals = Rental::with(['rentalDetails.heavyEquipment'])->where('user_id', $user->id)->orderByDesc('created_at')->get();
        }

        return Inertia::render('Rentals/History', [
            'rentals' => $rentals,
            'isAdmin' => $user->role === 'administrator',
        ]);
    }

    public function create()
    {
        $user = Auth::user();
        $heavyEquipments = HeavyEquipment::whereDoesntHave('activeRentals')->get();
        $customers = [];

        if ($user->role === 'administrator') {
            $customers = User::where('role', 'pelanggan')
                ->orderBy('name')
                ->get(['id', 'name', 'email']);
        }

        return Inertia::render('Rentals/Create', [
            'heavyEquipments' => $heavyEquipments,
            'isAdmin' => $user->role === 'administrator',
            'customers' => $customers,
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $isAdmin = $user->role === 'administrator';

        $rules = [
            'heavy_equipment_ids' => ['required', 'array', 'min:1'],
            'heavy_equipment_ids.*' => ['required', 'exists:heavy_equipments,id'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'payment_proof' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
        ];

        if ($isAdmin) {
            $rules['customer_id'] = [
                'required',
                Rule::exists('users', 'id')->where('role', 'pelanggan'),
            ];
        }

        $data = $request->validate($rules);

        $startDate = Carbon::parse($data['start_date']);
        $endDate = Carbon::parse($data['end_date']);
        $days = max(1, $startDate->diffInDays($endDate) + 1);

        $heavyEquipments = HeavyEquipment::whereIn('id', $data['heavy_equipment_ids'])->get();

        $totalPrice = $heavyEquipments->sum(fn ($equipment) => $equipment->daily_rate * $days);

        $path = $request->file('payment_proof')->store('payment_proofs', 'public');

        $customerId = $isAdmin ? $data['customer_id'] : $user->id;

        $rental = Rental::create([
            'user_id' => $customerId,
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'total_price' => $totalPrice,
            'payment_proof' => $path,
            'status' => 'Menunggu',
        ]);

        foreach ($heavyEquipments as $equipment) {
            RentalDetail::create([
                'rental_id' => $rental->id,
                'heavy_equipment_id' => $equipment->id,
                'unit_price' => $equipment->daily_rate,
            ]);
        }

        return redirect()->route('history')->with('success', 'Rental request submitted successfully.');
    }

    public function show(Rental $rental)
    {
        $user = Auth::user();

        if ($user->role === 'administrator' || $rental->user_id !== $user->id) {
            abort(403);
        }

        $rental->loadMissing([
            'user:id,name,email',
            'rentalDetails.heavyEquipment:id,name,photo',
        ]);

        if (! $rental->relationLoaded('user') || $rental->user === null) {
            $rental->setRelation('user', $user);
        }

        return Inertia::render('Rentals/Show', [
            'rental' => $rental,
            'isAdmin' => false,
        ]);
    }

    public function edit(Rental $rental)
    {
        $user = Auth::user();

        if ($user->role !== 'administrator') {
            abort(403);
        }

        $rental->loadMissing([
            'user:id,name,email',
            'rentalDetails.heavyEquipment:id,name,photo',
        ]);

        return Inertia::render('Rentals/Edit', [
            'rental' => $rental,
            'isAdmin' => true,
        ]);
    }

    public function update(Request $request, Rental $rental)
    {
        $user = Auth::user();

        if ($user->role !== 'administrator') {
            abort(403);
        }

        $data = $request->validate([
            'status' => ['required', 'in:Menunggu,Disetujui,Sedang Disewa,Selesai,Ditolak'],
        ]);

        $rental->update(['status' => $data['status']]);

        return redirect()->route('history')->with('success', 'Rental status updated.');
    }

    public function destroy(Rental $rental)
    {
        $user = Auth::user();

        if ($user->role !== 'administrator' && $rental->user_id !== $user->id) {
            abort(403);
        }

        if ($rental->status !== 'Menunggu') {
            abort(403, 'Only pending rentals can be canceled.');
        }

        $rental->update(['status' => 'Ditolak']);

        return redirect()->route('history')->with('success', 'Rental request canceled.');
    }
}
