<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TemplateLayer extends Model
{
    use HasFactory;

    protected $fillable = [
        'template_id',
        'key',
        'label',
        'type',
        'sort_order',
        'is_required',
        'x',
        'y',
        'width',
        'height',
        'font_family',
        'font_size',
        'font_weight',
        'color',
        'align',
        'direction',
        'line_height',
        'letter_spacing',
        'text_shadow',
        'placeholder',
        'border_radius',
        'object_fit',
        'placeholder_url',
    ];

    protected $casts = [
        'is_required' => 'boolean',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    public function isText(): bool
    {
        return $this->type === 'text';
    }

    public function isImage(): bool
    {
        return $this->type === 'image';
    }
}
