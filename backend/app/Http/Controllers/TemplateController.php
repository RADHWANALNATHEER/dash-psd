<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTemplateRequest;
use App\Models\Template;
use App\Repositories\TemplateRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TemplateController extends Controller
{
    public function __construct(private TemplateRepository $templates)
    {
    }

    public function index(): View
    {
        return view('templates.index', [
            'templates' => $this->templates->paginate(),
        ]);
    }

    public function create(): View
    {
        return view('templates.create');
    }

    public function store(StoreTemplateRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $backgroundPath = $request->file('background')->store('templates/backgrounds', config('rendering.disk'));

        $template = $this->templates->create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']).'-'.Str::random(6),
            'description' => $data['description'] ?? null,
            'background_path' => $backgroundPath,
            'width' => $data['width'],
            'height' => $data['height'],
            'is_active' => $data['is_active'] ?? true,
            'user_id' => $request->user()?->id,
        ]);

        foreach ($data['layers'] as $index => $layer) {
            $template->layers()->create([...$layer, 'sort_order' => $index]);
        }

        return redirect()->route('templates.index')->with('status', 'تم إنشاء القالب بنجاح');
    }

    public function edit(Template $template): View
    {
        $template->load('layers');

        return view('templates.edit', ['template' => $template]);
    }

    public function update(StoreTemplateRequest $request, Template $template): RedirectResponse
    {
        $data = $request->validated();

        $attributes = [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'width' => $data['width'],
            'height' => $data['height'],
            'is_active' => $data['is_active'] ?? $template->is_active,
        ];

        if ($request->hasFile('background')) {
            Storage::disk(config('rendering.disk'))->delete($template->background_path);
            $attributes['background_path'] = $request->file('background')->store('templates/backgrounds', config('rendering.disk'));
        }

        $this->templates->update($template, $attributes);

        $template->layers()->delete();
        foreach ($data['layers'] as $index => $layer) {
            $template->layers()->create([...$layer, 'sort_order' => $index]);
        }

        return redirect()->route('templates.index')->with('status', 'تم تحديث القالب بنجاح');
    }

    public function destroy(Template $template): RedirectResponse
    {
        Storage::disk(config('rendering.disk'))->delete($template->background_path);
        $this->templates->delete($template);

        return redirect()->route('templates.index')->with('status', 'تم حذف القالب');
    }
}
