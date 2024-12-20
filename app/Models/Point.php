<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Point extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    const TYPE_DEPOSIT = 'deposit';
    const TYPE_WITHDRAWAL = 'withdrawal';
    const TYPE_EXPIRATION = 'expiration';
    const EXPIRATION_DAYS = 1;


    const TYPES = [self::TYPE_DEPOSIT, self::TYPE_WITHDRAWAL, self::TYPE_EXPIRATION];

    public function model()
    {
        return $this->morphTo();
    }

    public function scopeMine(Builder $query): Builder
    {
        return $query->where('user_id', auth()->id());
    }


    public function scopeSearch(Builder $query, $filters)
    {
        if (!empty($filters['type'])) {
            if (in_array($filters['type'], self::TYPES)) {
                if ($filters['type'] === self::TYPE_EXPIRATION) {
                    $query->whereNotNull('expired_at');
                } else {
                    $query->whereNull('expired_at');
                    if ($filters['type'] === self::TYPE_DEPOSIT) {
                        $query->where('deposit', '>', 0);
                    }
                    if ($filters['type'] === self::TYPE_WITHDRAWAL) {
                        $query->where('withdrawal', '>', 0);
                    }
                }
            }
        }

    }
}
