<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Pseudonym extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'pseudonym',
        'real_value',
    ];

    /**
     * Scope a query to only include expired pseudonyms (older than 30 minutes).
     */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('created_at', '<', now()->subMinutes(30));
    }
}
