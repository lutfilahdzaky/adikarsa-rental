<?php

namespace App\Http\Controllers;

use App\Models\HeavyEquipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
            'photo' => 'nullable|file|image|max:5120', // max 5MB
        ]);

        // Handle uploaded photo file and store URL in validated data
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('heavy_equipments', 'public');
            // store the public URL so frontend can display it directly
            $validated['photo'] = Storage::url($path);
        }

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
            'photo' => 'nullable|file|image|max:5120',
        ]);

        if ($request->hasFile('photo')) {
            // delete previous file if it was stored under /storage (Storage::url)
            if ($heavyEquipment->photo) {
                // try to convert URL to storage path
                $previousPath = str_replace('/storage/', '', parse_url($heavyEquipment->photo, PHP_URL_PATH));
                if ($previousPath && Storage::disk('public')->exists($previousPath)) {
                    Storage::disk('public')->delete($previousPath);
                }
            }

            $path = $request->file('photo')->store('heavy_equipments', 'public');
            $validated['photo'] = Storage::url($path);
        }

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
