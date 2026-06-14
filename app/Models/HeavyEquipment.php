<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'description', 'daily_rate', 'photo', 'status'])]
class HeavyEquipment extends Model
{
    use HasFactory;

    protected $table = 'heavy_equipments';

    protected $casts = [
        'daily_rate' => 'integer',
    ];
}
