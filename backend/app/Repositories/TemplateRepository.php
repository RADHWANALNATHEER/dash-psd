<?php

namespace App\Repositories;

use App\Models\Template;
use Illuminate\Database\Eloquent\Collection;

class TemplateRepository
{
    public function allActive(): Collection
    {
        return Template::where('is_active', true)->latest()->get();
    }

    public function paginate(int $perPage = 15)
    {
        return Template::with('layers')->latest()->paginate($perPage);
    }

    public function findWithLayers(int $id): Template
    {
        return Template::with('layers')->findOrFail($id);
    }

    public function create(array $attributes): Template
    {
        return Template::create($attributes);
    }

    public function update(Template $template, array $attributes): Template
    {
        $template->update($attributes);

        return $template;
    }

    public function delete(Template $template): void
    {
        $template->delete();
    }
}
