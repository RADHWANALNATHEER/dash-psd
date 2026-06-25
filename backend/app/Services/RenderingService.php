<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class RenderingService
{
    /**
     * يرسل بيانات القالب والقيم إلى خدمة Node.js، ثم ينزّل الصورة الناتجة
     * ويخزّنها على قرص Laravel Filesystem المُعتمد (محلي أو S3/R2).
     *
     * @return string المسار النسبي للملف المخزَّن على القرص (storage path)
     */
    public function render(array $payload): string
    {
        $response = Http::baseUrl(config('rendering.base_url'))
            ->withToken(config('rendering.token'))
            ->timeout(config('rendering.timeout'))
            ->post('/render', $payload);

        if ($response->failed()) {
            throw new RuntimeException('فشل الاتصال بمحرك التصدير: '.$response->body());
        }

        $result = $response->json();

        if (empty($result['success'])) {
            throw new RuntimeException('فشل إنتاج الصورة: '.($result['message'] ?? 'خطأ غير معروف'));
        }

        $fileResponse = Http::baseUrl(config('rendering.base_url'))
            ->withToken(config('rendering.token'))
            ->timeout(config('rendering.timeout'))
            ->get('/files/'.$result['file_name']);

        if ($fileResponse->failed()) {
            throw new RuntimeException('فشل تنزيل الصورة المُنتَجة من محرك التصدير');
        }

        $extension = Str::afterLast($result['file_name'], '.');
        $storagePath = 'designs/'.now()->format('Y/m').'/'.Str::uuid().'.'.$extension;

        Storage::disk(config('rendering.disk'))->put($storagePath, $fileResponse->body());

        return $storagePath;
    }
}
