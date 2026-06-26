<?php

namespace App\Services;

use App\Models\Template;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use JsonException;
use RuntimeException;
use Throwable;

class PsdImportService
{
    /**
     * @param  array<int, UploadedFile>  $files
     * @return array{templates: array<int, Template>, skipped: array<int, string>}
     */
    public function importMany(array $files, int $userId, ?string $description = null): array
    {
        $templates = [];
        $skipped = [];

        foreach ($files as $file) {
            $result = $this->importFile($file, $userId, $description);
            $templates = [...$templates, ...$result['templates']];
            $skipped = [...$skipped, ...$result['skipped']];
        }

        return [
            'templates' => $templates,
            'skipped' => $skipped,
        ];
    }

    /**
     * @return array{templates: array<int, Template>, skipped: array<int, string>}
     */
    private function importFile(UploadedFile $file, int $userId, ?string $description = null): array
    {
        $workDir = storage_path('app/private/psd-imports/'.Str::uuid());
        File::ensureDirectoryExists($workDir);

        try {
            $report = $this->runImporter($file, $workDir);

            return [
                'templates' => $this->persistTemplates($report, $workDir, $userId, $description),
                'skipped' => $report['skipped'] ?? [],
            ];
        } finally {
            File::deleteDirectory($workDir);
        }
    }

    /**
     * @return array{name: string, templates: array<int, array<string, mixed>>, skipped?: array<int, string>}
     */
    private function runImporter(UploadedFile $file, string $workDir): array
    {
        $pythonBin = config('psd.python_bin', 'python3');
        $scriptPath = base_path('scripts/import_psd.py');

        $result = Process::path(base_path())
            ->timeout((int) config('psd.timeout', 180))
            ->run([
                $pythonBin,
                $scriptPath,
                '--input', $file->getRealPath(),
                '--output-dir', $workDir,
                '--document-name', pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            ]);

        if ($result->failed()) {
            $details = trim($result->errorOutput() ?: $result->output());
            throw new RuntimeException('تعذر تحليل ملف PSD.'.($details !== '' ? ' '.$details : ''));
        }

        $reportPath = $workDir.'/report.json';
        if (! File::exists($reportPath)) {
            throw new RuntimeException('تعذر إنشاء تقرير الاستيراد من ملف PSD.');
        }

        try {
            /** @var array{name: string, templates: array<int, array<string, mixed>>, skipped?: array<int, string>} $report */
            $report = json_decode(File::get($reportPath), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException('فشل قراءة تقرير الاستيراد الناتج من PSD.', 0, $exception);
        }

        if (empty($report['templates'])) {
            throw new RuntimeException('لم يتم العثور على قوالب قابلة للاستيراد داخل ملف PSD.');
        }

        return $report;
    }

    /**
     * @param  array{name: string, templates: array<int, array<string, mixed>>}  $report
     * @return array<int, Template>
     */
    private function persistTemplates(array $report, string $workDir, int $userId, ?string $description = null): array
    {
        $created = [];
        $storedBackgrounds = [];

        try {
            DB::transaction(function () use ($report, $workDir, $userId, $description, &$created, &$storedBackgrounds) {
                foreach ($report['templates'] as $templateData) {
                    $backgroundPath = $this->storeBackground(
                        $workDir.'/'.$templateData['background_file'],
                        $templateData['name'] ?? $report['name'] ?? 'template'
                    );

                    $storedBackgrounds[] = $backgroundPath;

                    $template = Template::create([
                        'name' => $templateData['name'] ?? ($report['name'] ?? 'قالب مستورد'),
                        'slug' => $this->buildSlug($templateData['name'] ?? ($report['name'] ?? 'template')),
                        'description' => $description,
                        'background_path' => $backgroundPath,
                        'width' => (int) $templateData['width'],
                        'height' => (int) $templateData['height'],
                        'is_active' => true,
                        'user_id' => $userId,
                    ]);

                    foreach (($templateData['layers'] ?? []) as $index => $layer) {
                        $template->layers()->create([
                            'key' => $layer['key'],
                            'label' => $layer['label'],
                            'type' => $layer['type'],
                            'sort_order' => $index,
                            'is_required' => (bool) ($layer['is_required'] ?? true),
                            'x' => (int) $layer['x'],
                            'y' => (int) $layer['y'],
                            'width' => (int) $layer['width'],
                            'height' => isset($layer['height']) ? (int) $layer['height'] : null,
                            'font_family' => $layer['font_family'] ?? null,
                            'font_size' => isset($layer['font_size']) ? (int) $layer['font_size'] : null,
                            'font_weight' => isset($layer['font_weight']) ? (int) $layer['font_weight'] : null,
                            'color' => $layer['color'] ?? null,
                            'align' => $layer['align'] ?? null,
                            'direction' => $layer['direction'] ?? null,
                            'line_height' => isset($layer['line_height']) ? (float) $layer['line_height'] : null,
                            'letter_spacing' => isset($layer['letter_spacing']) ? (float) $layer['letter_spacing'] : null,
                            'text_shadow' => $layer['text_shadow'] ?? null,
                            'placeholder' => $layer['placeholder'] ?? null,
                            'border_radius' => isset($layer['border_radius']) ? (int) $layer['border_radius'] : null,
                            'object_fit' => $layer['object_fit'] ?? null,
                            'placeholder_url' => $layer['placeholder_url'] ?? null,
                        ]);
                    }

                    $created[] = $template->load('layers');
                }
            });
        } catch (Throwable $exception) {
            Storage::disk(config('rendering.disk'))->delete($storedBackgrounds);

            throw $exception;
        }

        return $created;
    }

    private function storeBackground(string $sourcePath, string $templateName): string
    {
        if (! is_file($sourcePath)) {
            throw new RuntimeException('صورة الخلفية المستخرجة من PSD غير موجودة.');
        }

        $targetPath = 'templates/backgrounds/imported/'.($this->slugBase($templateName) ?: 'template').'-'.Str::random(10).'.png';
        $stream = fopen($sourcePath, 'rb');

        if ($stream === false) {
            throw new RuntimeException('تعذر قراءة الخلفية المستخرجة من ملف PSD.');
        }

        try {
            $stored = Storage::disk(config('rendering.disk'))->put($targetPath, $stream);
        } finally {
            fclose($stream);
        }

        if (! $stored) {
            throw new RuntimeException('تعذر حفظ الخلفية المستخرجة داخل التطبيق.');
        }

        return $targetPath;
    }

    private function buildSlug(string $name): string
    {
        return ($this->slugBase($name) ?: 'template').'-'.Str::random(6);
    }

    private function slugBase(string $name): string
    {
        return Str::slug(Str::ascii($name));
    }
}
