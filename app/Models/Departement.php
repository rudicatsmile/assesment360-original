<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Departement extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'urut',
        'description',
        'show_in_analytics',
    ];

    protected $casts = [
        'show_in_analytics' => 'boolean',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'department_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class, 'department_id');
    }

    public function answerOptions(): HasMany
    {
        return $this->hasMany(AnswerOption::class, 'department_id');
    }

    public function evaluators(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'user_evaluable_departments',
            'department_id',
            'user_id'
        )->withTimestamps();
    }
}
