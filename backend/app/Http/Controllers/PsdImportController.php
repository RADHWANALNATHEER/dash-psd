<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImportPsdTemplatesRequest;
use App\Models\Template;
use App\Services\PsdImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PsdImportController extends Controller
{
    public function __construct(private PsdImportService $importer)
    {
    }

    public function create(): View
    {
        $this->authorize('create', Template::class);

        return view('templates.import');
    }

    public function store(ImportPsdTemplatesRequest $request): RedirectResponse
    {
        $this->authorize('create', Template::class);

        $result = $this->importer->importMany(
            $request->file('psd_files'),
            $request->user()->id,
            $request->validated('description')
        );

        $status = sprintf('تم استيراد %d قالب من ملفات PSD.', count($result['templates']));

        if (! empty($result['skipped'])) {
            $status .= ' تم تجاوز: '.implode('، ', $result['skipped']);
        }

        return redirect()->route('templates.index')->with('status', $status);
    }
}
