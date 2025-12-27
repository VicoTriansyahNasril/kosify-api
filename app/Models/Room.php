<?php

namespace App\Models;

use App\Enums\RoomStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Room extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'boarding_house_id',
        'name',
        'price',
        'status',
        'capacity',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:0',
            'status' => RoomStatus::class,
            'capacity' => 'integer',
        ];
    }

    public function scopeSearch(Builder $query, ?string $keyword): void
    {
        if (!empty($keyword)) {
            $query->where('name', 'like', "%{$keyword}%");
        }
    }

    public function scopeStatus(Builder $query, ?string $status): void
    {
        if (!empty($status)) {
            $query->where('status', $status);
        }
    }

    public function boardingHouse(): BelongsTo
    {
        return $this->belongsTo(BoardingHouse::class);
    }

    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class);
    }

    public function activeTenant()
    {
        return $this->hasOne(Tenant::class)->where('status', 'active')->latest();
    }

    public function images(): HasMany
    {
        return $this->hasMany(RoomImage::class);
    }
}