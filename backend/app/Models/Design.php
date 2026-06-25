<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Design extends Model
{
    use HasFactory;

    protected $fillable = [
        'template_id',
        'user_id',
        'design_job_id',
        'values',
        'format',
        'width',
        'height',
        'output_path',
        'status',
        'failure_reason',
    ];

    protected $casts = [
        'values' => 'array',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function designJob(): BelongsTo
    {
        return $this->belongsTo(DesignJob::class);
    }

    public function getOutputUrlAttribute(): ?string
    {
        return $this->output_path ? Storage::disk(config('rendering.disk'))->url($this->output_path) : null;
    }
}
