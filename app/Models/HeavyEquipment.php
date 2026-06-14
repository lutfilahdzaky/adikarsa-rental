<?php

namespace App\Models;

use App\Models\Rental;
use App\Models\RentalDetail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

#[Fillable(['name', 'description', 'daily_rate', 'photo'])]
class HeavyEquipment extends Model
{
    use HasFactory;

    protected $table = 'heavy_equipments';

    protected $casts = [
        'daily_rate' => 'integer',
    ];

    public function rentalDetails(): HasMany
    {
        return $this->hasMany(RentalDetail::class);
    }

    public function rentals(): HasManyThrough
    {
        return $this->hasManyThrough(
            Rental::class,
            RentalDetail::class,
            'heavy_equipment_id',
            'id',
            'id',
            'rental_id'
        );
    }

    public function activeRentals(): HasManyThrough
    {
        return $this->rentals()->whereIn('status', ['Menunggu', 'Disetujui', 'Sedang Disewa']);
    }

    public function getStatusAttribute($value): string
    {
        if ($this->relationLoaded('activeRentals')) {
            return $this->activeRentals->isNotEmpty() ? 'rented' : 'available';
        }

        if (isset($this->active_rentals_count)) {
            return $this->active_rentals_count > 0 ? 'rented' : 'available';
        }

        return $this->activeRentals()->exists() ? 'rented' : 'available';
    }
}
