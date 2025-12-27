<?php

namespace App\Models;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Transaction extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'room_id',
        'invoice_number',
        'type',
        'amount',
        'status',
        'due_date',
        'paid_at',
        'payment_proof',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:0',
            'due_date' => 'date',
            'paid_at' => 'datetime',
            'status' => TransactionStatus::class,
            'type' => TransactionType::class,
        ];
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->invoice_number)) {
                $model->invoice_number = 'INV/' . date('Ymd') . '/' . strtoupper(Str::random(5));
            }
        });
    }

    public function scopeForBoardingHouse(Builder $query, string $boardingHouseId): void
    {
        $query->whereHas('room', function ($q) use ($boardingHouseId) {
            $q->where('boarding_house_id', $boardingHouseId);
        });
    }

    public function scopeSearch(Builder $query, ?string $keyword): void
    {
        if (!empty($keyword)) {
            $query->where(function ($q) use ($keyword) {
                $q->where('invoice_number', 'like', "%{$keyword}%")
                    ->orWhereHas('tenant', function ($sq) use ($keyword) {
                        $sq->where('name', 'like', "%{$keyword}%");
                    });
            });
        }
    }

    public function scopeStatus(Builder $query, ?string $status): void
    {
        if (!empty($status)) {
            $query->where('status', $status);
        }
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }
}