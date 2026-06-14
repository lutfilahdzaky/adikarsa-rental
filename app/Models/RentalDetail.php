<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['rental_id', 'heavy_equipment_id', 'unit_price'])]
class RentalDetail extends Model
{
    use HasFactory;

    protected $casts = [
        'unit_price' => 'integer',
    ];

    public function rental(): BelongsTo
    {
        return $this->belongsTo(Rental::class);
    }

    public function heavyEquipment(): BelongsTo
    {
        return $this->belongsTo(HeavyEquipment::class);
    }
}
