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
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">تجربة القالب شاشة بشاشة</h2>
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
            step: 0,
            get totalSteps() { return this.layers.length + 1 },
            get currentLayer() { return this.step < this.layers.length ? this.layers[this.step] : null },
            get isReviewStep() { return this.step === this.layers.length },
            next() { if (this.step < this.totalSteps - 1) this.step++ },
            back() { if (this.step > 0) this.step-- },
            goTo(i) { this.step = i },
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
                <!-- مؤشر الخطوات -->
                <div class="bg-white shadow-sm sm:rounded-lg p-4 mb-6">
                    <div class="flex items-center gap-2 overflow-x-auto">
                        <template x-for="(layer, i) in layers" :key="layer.key">
                            <button type="button" @click="goTo(i)"
                                    class="shrink-0 flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-medium"
                                    :class="step === i ? 'bg-indigo-600 text-white' : (step > i ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500')">
                                <span x-text="i + 1"></span>
                                <span x-text="layer.label"></span>
                            </button>
                        </template>
                        <button type="button" @click="goTo(layers.length)"
                                class="shrink-0 flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-medium"
                                :class="isReviewStep ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-500'">
                            <span x-text="layers.length + 1"></span>
                            <span>المراجعة والتصدير</span>
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- شاشة الإدخال الحالية -->
                    <div class="bg-white shadow-sm sm:rounded-lg p-6">
                        <form method="POST" action="{{ route('designs.store') }}" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="template_id" value="{{ $selectedTemplate->id }}">

                            @foreach ($selectedTemplate->layers as $i => $layer)
                                <div x-show="step === {{ $i }}" x-cloak>
                                    <p class="text-sm text-gray-400 mb-1">الخطوة {{ $i + 1 }} من <span x-text="totalSteps"></span></p>
                                    <x-input-label :value="$layer->label.($layer->is_required ? ' *' : '')" />

                                    @if ($layer->isText())
                                        <textarea name="values[{{ $layer->key }}]" rows="3"
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

                            <div x-show="isReviewStep" x-cloak>
                                <p class="text-sm text-gray-400 mb-1">الخطوة <span x-text="totalSteps"></span> من <span x-text="totalSteps"></span></p>
                                <h3 class="font-bold text-gray-800 mb-3">راجع البيانات قبل التصدير</h3>
                                <ul class="text-sm text-gray-600 space-y-1 mb-4">
                                    @foreach ($selectedTemplate->layers as $layer)
                                        <li>
                                            <span class="font-medium">{{ $layer->label }}:</span>
                                            @if ($layer->isText())
                                                <span x-text="values['{{ $layer->key }}'] || '—'"></span>
                                            @else
                                                <span x-text="previews['{{ $layer->key }}'] ? 'تم اختيار صورة' : 'لم يتم اختيار صورة'"></span>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>

                                <x-input-label for="format" value="صيغة التصدير" />
                                <select id="format" name="format" class="mt-1 block w-full border-gray-300 rounded-md">
                                    <option value="png">PNG</option>
                                    <option value="jpg">JPG</option>
                                </select>
                            </div>

                            <div class="mt-6 flex justify-between">
                                <button type="button" @click="back()" x-show="step > 0"
                                        class="px-4 py-2 text-sm text-gray-600 hover:underline">السابق</button>
                                <span x-show="step === 0"></span>

                                <button type="button" @click="next()" x-show="!isReviewStep"
                                        class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                                    التالي
                                </button>
                                <x-primary-button x-show="isReviewStep">تصدير التصميم</x-primary-button>
                            </div>
                        </form>
                    </div>

                    <!-- معاينة فورية -->
                    <div class="bg-white shadow-sm sm:rounded-lg p-6">
                        <h3 class="font-bold text-gray-800 mb-4">معاينة فورية</h3>
                        <div class="relative bg-gray-100 mx-auto overflow-hidden border"
                             :style="`width:${template.width * scale}px;height:${template.height * scale}px;background-image:url('${template.background_url}');background-size:cover;`">
                            <template x-for="(layer, i) in layers" :key="layer.key">
                                <div x-show="layer.type === 'text'"
                                     class="absolute whitespace-pre-wrap transition-all"
                                     :class="step === i ? 'ring-2 ring-indigo-500' : ''"
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
                            <template x-for="(layer, i) in layers" :key="'img-' + layer.key">
                                <img x-show="layer.type === 'image' && previews[layer.key]"
                                     :src="previews[layer.key]"
                                     class="absolute object-cover transition-all"
                                     :class="step === i ? 'ring-2 ring-indigo-500' : ''"
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
                    اختر قالبًا من القائمة أعلاه لبدء تجربته شاشة بشاشة.
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
