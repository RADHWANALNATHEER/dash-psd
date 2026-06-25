<?php

namespace App\Jobs;

use App\Models\Design;
use App\Repositories\DesignRepository;
use App\Services\RenderingService;
use App\Services\TemplateBuilderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use RuntimeException;

/**
 * يُنتج تصميمًا واحدًا بشكل غير متزامن عبر طابور Redis (يُستخدم في المعالجة الدفعية - مرحلة 2).
 */
class GenerateDesignJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(private Design $design)
    {
    }

    public function handle(TemplateBuilderService $templateBuilder, RenderingService $renderingService, DesignRepository $designs): void
    {
        $template = $this->design->template;

        try {
            $payload = $templateBuilder->buildPayload(
                $template,
                $this->design->values,
                $this->design->format,
                $this->design->width,
                $this->design->height,
            );

            $outputPath = $renderingService->render($payload);
            $designs->markCompleted($this->design, $outputPath);
        } catch (RuntimeException $exception) {
            $designs->markFailed($this->design, $exception->getMessage());

            throw $exception;
        }
    }
}
