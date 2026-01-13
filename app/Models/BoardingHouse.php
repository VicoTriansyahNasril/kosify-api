<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class BoardingHouse extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'address',
        'description',
        'category',
        'facilities',
        'rules',
        'cover_image',
        'latitude',
        'longitude',
    ];

    protected $appends = ['rating_avg'];

    protected function casts(): array
    {
        return [
            'facilities' => 'array',
            'rules' => 'array',
        ];
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->name) . '-' . Str::random(6);
            }
        });
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function getRatingAvgAttribute(): float
    {
        if ($this->relationLoaded('reviews')) {
            return round($this->reviews->avg('rating') ?? 0, 1);
        }

        return round($this->reviews()->avg('rating') ?? 0, 1);
    }

    public function scopeSearch(Builder $query, ?string $keyword): void
    {
        if (!empty($keyword)) {
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('address', 'like', "%{$keyword}%");
            });
        }
    }

    public function scopePriceBetween(Builder $query, int $min, int $max): void
    {
        $query->whereHas('rooms', function ($q) use ($min, $max) {
            $q->whereBetween('price', [$min, $max]);
        });
    }

    public function scopeWithFacilities(Builder $query, array $facilities): void
    {
        foreach ($facilities as $facility) {
            $query->whereJsonContains('facilities', $facility);
        }
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }
}