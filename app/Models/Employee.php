<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'manager_id',
        'name',
        'email',
        'cpf',
        'city',
        'state',
    ];

    /**
     * Scope to get employee managed by current user
     *
     * @param  Builder  $query
     * @return void
     */
    public function scopeCurrentUser(Builder $query): void
    {
        $query->where('manager_id', Auth::id());
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }
}
