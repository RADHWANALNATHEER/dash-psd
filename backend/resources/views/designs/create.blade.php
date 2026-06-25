@php
    $layersData = $selectedTemplate
        ? $selectedTemplate->layers->map(fn ($l) => $l->only([
            'key', 'label', 'type', 'x', 'y', 'width', 'height', 'font_family', 'font_size',
            'font_weight', 'color', 'align', 'direction', 'line_height', 'letter_spacing',
            'text_shadow', 'placeholder', 'border_radius', 'object_fit', 'is_required',
        ]))->all()
        : [];
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">إنتاج تصميم</h2>
    </x-slot>

    <div class="py-12" x-data="{
            template: @js($selectedTemplate ? [
                'id' => $selectedTemplate->id,
                'width' => $selectedTemplate->width,
                'height' => $selectedTemplate->height,
                'background_url' => $selectedTemplate->background_url,
            ] : null),
            layers: @js($layersData),
            values: {},
            previews: {},
            scale: 1,
        }"
        x-init="
            scale = template ? Math.min(560 / template.width, 1) : 1;
            layers.forEach(l => { if (l.type === 'text') values[l.key] = l.placeholder || ''; });
        ">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-100 text-red-800 rounded-md">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg p-6 mb-6">
                <form method="GET" action="{{ route('designs.create') }}" class="flex items-end gap-3">
                    <div class="flex-1">
                        <x-input-label for="template_id" value="اختر القالب" />
                        <select id="template_id" name="template_id" onchange="this.form.submit()" class="mt-1 block w-full border-gray-300 rounded-md">
                            <option value="">— اختر قالبًا —</option>
                            @foreach ($templates as $t)
                                <option value="{{ $t->id }}" {{ $selectedTemplate?->id === $t->id ? 'selected' : '' }}>{{ $t->name }} ({{ $t->width }}×{{ $t->height }})</option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>

            @if ($selectedTemplate)
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- نموذج الإدخال -->
                    <div class="bg-white shadow-sm sm:rounded-lg p-6">
                        <form method="POST" action="{{ route('designs.store') }}" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="template_id" value="{{ $selectedTemplate->id }}">

                            <div class="mb-4">
                                <x-input-label for="format" value="صيغة التصدير" />
                                <select id="format" name="format" class="mt-1 block w-full border-gray-300 rounded-md">
                                    <option value="png">PNG</option>
                                    <option value="jpg">JPG</option>
                                </select>
                            </div>

                            @foreach ($selectedTemplate->layers as $layer)
                                <div class="mb-4">
                                    <x-input-label :value="$layer->label.($layer->is_required ? ' *' : '')" />

                                    @if ($layer->isText())
                                        <textarea name="values[{{ $layer->key }}]" rows="2"
                                                  x-model="values['{{ $layer->key }}']"
                                                  {{ $layer->is_required ? 'required' : '' }}
                                                  class="mt-1 block w-full border-gray-300 rounded-md" dir="rtl"
                                                  placeholder="{{ $layer->placeholder }}"></textarea>
                                    @else
                                        <input type="file" name="values[{{ $layer->key }}]" accept="image/*"
                                               {{ $layer->is_required ? 'required' : '' }}
                                               @change="
                                                    const file = $event.target.files[0];
                                                    if (file) previews['{{ $layer->key }}'] = URL.createObjectURL(file);
                                               "
                                               class="mt-1 block w-full text-sm">
                                    @endif
                                </div>
                            @endforeach

                            <div class="mt-6 flex justify-end">
                                <x-primary-button>تصدير التصميم</x-primary-button>
                            </div>
                        </form>
                    </div>

                    <!-- معاينة فورية -->
                    <div class="bg-white shadow-sm sm:rounded-lg p-6">
                        <h3 class="font-bold text-gray-800 mb-4">معاينة فورية</h3>
                        <div class="relative bg-gray-100 mx-auto overflow-hidden border"
                             :style="`width:${template.width * scale}px;height:${template.height * scale}px;background-image:url('${template.background_url}');background-size:cover;`">
                            <template x-for="layer in layers" :key="layer.key">
                                <div x-show="layer.type === 'text'"
                                     class="absolute whitespace-pre-wrap"
                                     :style="`
                                        left:${layer.x * scale}px;
                                        top:${layer.y * scale}px;
                                        width:${layer.width * scale}px;
                                        font-family:'${layer.font_family || 'Cairo'}', sans-serif;
                                        font-size:${(layer.font_size || 24) * scale}px;
                                        font-weight:${layer.font_weight || 400};
                                        color:${layer.color || '#000'};
                                        text-align:${layer.align || 'right'};
                                        direction:${layer.direction || 'rtl'};
                                        line-height:${layer.line_height || 1.3};
                                     `"
                                     x-text="values[layer.key]"></div>
                            </template>
                            <template x-for="layer in layers" :key="'img-' + layer.key">
                                <img x-show="layer.type === 'image' && previews[layer.key]"
                                     :src="previews[layer.key]"
                                     class="absolute object-cover"
                                     :style="`
                                        left:${layer.x * scale}px;
                                        top:${layer.y * scale}px;
                                        width:${layer.width * scale}px;
                                        height:${(layer.height || layer.width) * scale}px;
                                        border-radius:${(layer.border_radius || 0) * scale}px;
                                     `">
                            </template>
                        </div>
                        <p class="text-xs text-gray-500 mt-3">* المعاينة تقريبية للتحقق من المواضع، والصورة النهائية تُنتَج عبر محرك التصدير بدقة كاملة.</p>
                    </div>
                </div>
            @else
                <div class="bg-white shadow-sm sm:rounded-lg p-10 text-center text-gray-500">
                    اختر قالبًا من القائمة أعلاه لبدء إنتاج التصميم.
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
