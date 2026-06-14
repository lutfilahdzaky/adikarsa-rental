<?php

namespace App\Http\Controllers;

use App\Models\HeavyEquipment;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HeavyEquipmentController extends Controller
{
    public function index()
    {
        return Inertia::render('HeavyEquipments/Index', [
            'heavyEquipments' => HeavyEquipment::withCount('activeRentals')->orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('HeavyEquipments/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'daily_rate' => 'required|integer|min:0',
            'photo' => 'nullable|string',
        ]);

        HeavyEquipment::create($validated);

        return redirect()->route('heavy-equipments.index')
            ->with('success', 'Heavy equipment created successfully.');
    }

    public function edit(HeavyEquipment $heavyEquipment)
    {
        return Inertia::render('HeavyEquipments/Edit', [
            'heavyEquipment' => $heavyEquipment,
        ]);
    }

    public function update(Request $request, HeavyEquipment $heavyEquipment)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'daily_rate' => 'required|integer|min:0',
            'photo' => 'nullable|string',
        ]);

        $heavyEquipment->update($validated);

        return redirect()->route('heavy-equipments.index')
            ->with('success', 'Heavy equipment updated successfully.');
    }

    public function destroy(HeavyEquipment $heavyEquipment)
    {
        $heavyEquipment->delete();

        return redirect()->route('heavy-equipments.index')
            ->with('success', 'Heavy equipment deleted successfully.');
    }
}
