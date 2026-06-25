<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DesignJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'template_id',
        'user_id',
        'payload',
        'status',
        'total_count',
        'completed_count',
        'failed_count',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function designs(): HasMany
    {
        return $this->hasMany(Design::class);
    }
}
