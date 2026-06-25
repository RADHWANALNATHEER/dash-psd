<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Template extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'background_path',
        'width',
        'height',
        'is_active',
        'user_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function layers(): HasMany
    {
        return $this->hasMany(TemplateLayer::class)->orderBy('sort_order');
    }

    public function designs(): HasMany
    {
        return $this->hasMany(Design::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getBackgroundUrlAttribute(): string
    {
        return Storage::disk(config('rendering.disk'))->url($this->background_path);
    }
}
