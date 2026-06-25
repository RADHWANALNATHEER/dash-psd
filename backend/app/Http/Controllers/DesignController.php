<?php

namespace App\Http\Controllers;

use App\Models\Design;
use App\Models\Template;
use App\Repositories\DesignRepository;
use App\Repositories\TemplateRepository;
use App\Services\RenderingService;
use App\Services\TemplateBuilderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use RuntimeException;

class DesignController extends Controller
{
    public function __construct(
        private TemplateRepository $templates,
        private DesignRepository $designs,
        private TemplateBuilderService $templateBuilder,
        private RenderingService $renderingService,
    ) {
    }

    public function create(Request $request): View
    {
        $selectedTemplate = null;

        if ($request->filled('template_id')) {
            $selectedTemplate = $this->templates->findWithLayers((int) $request->get('template_id'));
        }

        return view('designs.create', [
            'templates' => $this->templates->allActive(),
            'selectedTemplate' => $selectedTemplate,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'template_id' => ['required', 'exists:templates,id'],
            'format' => ['required', 'in:png,jpg'],
        ]);

        $template = $this->templates->findWithLayers((int) $request->input('template_id'));
        $values = $this->collectLayerValues($request, $template);

        $design = $this->designs->create([
            'template_id' => $template->id,
            'user_id' => $request->user()?->id,
            'values' => $values,
            'format' => $request->input('format'),
            'width' => $template->width,
            'height' => $template->height,
            'status' => 'processing',
        ]);

        try {
            $payload = $this->templateBuilder->buildPayload($template, $values, $request->input('format'));
            $outputPath = $this->renderingService->render($payload);
            $this->designs->markCompleted($design, $outputPath);
        } catch (RuntimeException $exception) {
            $this->designs->markFailed($design, $exception->getMessage());

            return back()->withErrors(['design' => 'فشل إنتاج التصميم: '.$exception->getMessage()]);
        }

        return redirect()->route('designs.gallery')->with('status', 'تم إنتاج التصميم بنجاح');
    }

    public function gallery(Request $request): View
    {
        return view('designs.gallery', [
            'designs' => $this->designs->paginateForUser($request->user()->id),
        ]);
    }

    public function destroy(Design $design): RedirectResponse
    {
        if ($design->output_path) {
            Storage::disk(config('rendering.disk'))->delete($design->output_path);
        }

        $design->delete();

        return redirect()->route('designs.gallery')->with('status', 'تم حذف التصميم');
    }

    /**
     * يجمع قيم الطبقات من الطلب: نص مباشر للطبقات النصية، ورفع ملف للطبقات الصورية.
     */
    private function collectLayerValues(Request $request, Template $template): array
    {
        $values = [];

        foreach ($template->layers as $layer) {
            if ($layer->isText()) {
                $values[$layer->key] = $request->input("values.{$layer->key}");
                continue;
            }

            if ($request->hasFile("values.{$layer->key}")) {
                $path = $request->file("values.{$layer->key}")->store('uploads', config('rendering.disk'));
                $values[$layer->key] = Storage::disk(config('rendering.disk'))->url($path);
            }
        }

        return $values;
    }
}
