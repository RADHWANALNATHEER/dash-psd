<?php

namespace App\Services;

use App\Models\Template;
use Illuminate\Support\Facades\Storage;

class TemplateBuilderService
{
    /**
     * يحوّل القالب وطبقاته إلى البنية JSON التي يتوقعها محرك التصدير (Node.js).
     */
    public function buildPayload(Template $template, array $values, string $format = 'png', ?int $width = null, ?int $height = null): array
    {
        return [
            'template' => [
                'width' => $width ?? $template->width,
                'height' => $height ?? $template->height,
                'background_url' => $this->toDataUri($template->background_path),
                'layers' => $template->layers->map(fn ($layer) => $this->mapLayer($layer))->all(),
            ],
            'values' => $this->resolveValueUris($values),
            'format' => $format === 'jpg' ? 'jpeg' : $format,
        ];
    }

    /**
     * يحوّل أي قيمة تشبه مسار/رابط صورة محلي إلى data URI لتجنّب اضطرار محرك
     * التصدير لطلب الصورة عبر HTTP من نفس تطبيق Laravel (يمنع التعطل المتزامن).
     */
    private function resolveValueUris(array $values): array
    {
        $disk = config('rendering.disk');

        foreach ($values as $key => $value) {
            if (is_string($value) && $value !== '') {
                $path = $this->extractStoragePath($value);
                if ($path !== null && Storage::disk($disk)->exists($path)) {
                    $values[$key] = $this->toDataUri($path);
                }
            }
        }

        return $values;
    }

    private function extractStoragePath(string $value): ?string
    {
        $storagePrefix = '/storage/';
        $pos = strpos($value, $storagePrefix);

        if ($pos === false) {
            return null;
        }

        return substr($value, $pos + strlen($storagePrefix));
    }

    private function toDataUri(string $path): string
    {
        $disk = config('rendering.disk');
        $contents = Storage::disk($disk)->get($path);
        $mime = Storage::disk($disk)->mimeType($path) ?: 'image/png';

        return 'data:'.$mime.';base64,'.base64_encode($contents);
    }

    private function mapLayer($layer): array
    {
        $base = [
            'key' => $layer->key,
            'type' => $layer->type,
            'x' => $layer->x,
            'y' => $layer->y,
            'width' => $layer->width,
        ];

        if ($layer->isText()) {
            return array_merge($base, [
                'font_family' => $layer->font_family,
                'font_size' => $layer->font_size,
                'font_weight' => $layer->font_weight,
                'color' => $layer->color,
                'align' => $layer->align,
                'direction' => $layer->direction,
                'line_height' => $layer->line_height,
                'letter_spacing' => $layer->letter_spacing,
                'text_shadow' => $layer->text_shadow,
                'placeholder' => $layer->placeholder,
            ]);
        }

        return array_merge($base, [
            'height' => $layer->height,
            'border_radius' => $layer->border_radius,
            'object_fit' => $layer->object_fit,
            'placeholder_url' => $layer->placeholder_url,
        ]);
    }
}
