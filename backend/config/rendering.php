<?php

return [
    // عنوان خدمة Node.js + Puppeteer المسؤولة عن إنتاج الصور
    'base_url' => env('RENDER_SERVICE_URL', 'http://localhost:4000'),

    // التوكن المشترك بين Laravel وخدمة التصدير (يجب أن يطابق RENDER_SERVICE_TOKEN في .env الخاص بالخدمة)
    'token' => env('RENDER_SERVICE_TOKEN'),

    'timeout' => env('RENDER_SERVICE_TIMEOUT', 30),

    // قرص Laravel Filesystem المستخدم لتخزين الخلفيات والتصاميم الناتجة (local أو s3)
    'disk' => env('RENDERING_DISK', 'public'),
];
