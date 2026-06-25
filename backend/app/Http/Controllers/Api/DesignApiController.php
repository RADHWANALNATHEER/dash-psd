<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDesignRequest;
use App\Repositories\DesignRepository;
use App\Repositories\TemplateRepository;
use App\Services\RenderingService;
use App\Services\TemplateBuilderService;
use Illuminate\Http\JsonResponse;
use RuntimeException;

class DesignApiController extends Controller
{
    public function __construct(
        private TemplateRepository $templates,
        private DesignRepository $designs,
        private TemplateBuilderService $templateBuilder,
        private RenderingService $renderingService,
    ) {
    }

    /**
     * إنتاج تصميم واحد بشكل متزامن عبر API (مرحلة 2).
     * مثال: POST /api/v1/designs { template_id, format, values: { title: "...", photo: "https://..." } }
     */
    public function store(StoreDesignRequest $request): JsonResponse
    {
        $data = $request->validated();
        $template = $this->templates->findWithLayers($data['template_id']);

        $design = $this->designs->create([
            'template_id' => $template->id,
            'user_id' => $request->user()?->id,
            'values' => $data['values'],
            'format' => $data['format'],
            'width' => $template->width,
            'height' => $template->height,
            'status' => 'processing',
        ]);

        try {
            $payload = $this->templateBuilder->buildPayload($template, $data['values'], $data['format']);
            $outputPath = $this->renderingService->render($payload);
            $this->designs->markCompleted($design, $outputPath);
        } catch (RuntimeException $exception) {
            $this->designs->markFailed($design, $exception->getMessage());

            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json([
            'id' => $design->id,
            'status' => $design->status,
            'url' => $design->output_url,
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $design = $this->designs->paginateForUser(auth()->id())->getCollection()->firstWhere('id', $id);

        if (! $design) {
            return response()->json(['message' => 'التصميم غير موجود'], 404);
        }

        return response()->json([
            'id' => $design->id,
            'status' => $design->status,
            'url' => $design->output_url,
        ]);
    }
}
