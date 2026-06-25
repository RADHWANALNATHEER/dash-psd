@php
    $initialLayers = old('layers', $template?->layers->map(fn ($l) => $l->only([
        'key', 'label', 'type', 'x', 'y', 'width', 'height', 'font_family', 'font_size',
        'font_weight', 'color', 'align', 'direction', 'line_height', 'letter_spacing',
        'text_shadow', 'placeholder', 'border_radius', 'object_fit', 'is_required',
    ]))->all() ?? []);
@endphp

<form method="POST" action="{{ $action }}" enctype="multipart/form-data"
      x-data="{ layers: @js($initialLayers ?: []) }">
    @csrf
    @if ($method === 'PUT')
        @method('PUT')
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
        <div>
            <x-input-label for="name" value="اسم القالب" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $template?->name)" required />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div class="flex items-center gap-2 mt-6">
            <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $template?->is_active ?? true) ? 'checked' : '' }} class="rounded border-gray-300">
            <x-input-label for="is_active" value="قالب مُفعَّل" />
        </div>

        <div class="sm:col-span-2">
            <x-input-label for="description" value="وصف (اختياري)" />
            <textarea id="description" name="description" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">{{ old('description', $template?->description) }}</textarea>
        </div>

        <div>
            <x-input-label for="width" value="عرض القالب (px)" />
            <x-text-input id="width" name="width" type="number" class="mt-1 block w-full" :value="old('width', $template?->width ?? 1080)" required />
        </div>

        <div>
            <x-input-label for="height" value="ارتفاع القالب (px)" />
            <x-text-input id="height" name="height" type="number" class="mt-1 block w-full" :value="old('height', $template?->height ?? 1080)" required />
        </div>

        <div class="sm:col-span-2">
            <x-input-label for="background" value="صورة الخلفية (PNG)" />
            <input id="background" name="background" type="file" accept="image/*" class="mt-1 block w-full text-sm">
            @if ($template)
                <img src="{{ $template->background_url }}" class="mt-2 h-32 rounded border" alt="الخلفية الحالية">
            @endif
            <x-input-error :messages="$errors->get('background')" class="mt-2" />
        </div>
    </div>

    <div class="border-t pt-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-bold text-gray-800">الطبقات (نصوص وصور)</h3>
            <button type="button"
                    @click="layers.push({ key: '', label: '', type: 'text', x: 0, y: 0, width: 300, height: null, font_family: 'Cairo', font_size: 32, font_weight: 400, color: '#000000', align: 'right', direction: 'rtl', line_height: 1.3, letter_spacing: 0, text_shadow: '', placeholder: '', border_radius: 0, object_fit: 'cover', is_required: false })"
                    class="text-sm bg-indigo-600 text-white px-3 py-1.5 rounded-md hover:bg-indigo-700">
                + إضافة طبقة
            </button>
        </div>

        <template x-for="(layer, index) in layers" :key="index">
            <div class="border rounded-lg p-4 mb-4 bg-gray-50">
                <div class="flex justify-between items-center mb-3">
                    <span class="text-sm font-semibold text-gray-600" x-text="'طبقة #' + (index + 1)"></span>
                    <button type="button" @click="layers.splice(index, 1)" class="text-red-600 text-sm hover:underline">حذف</button>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <div>
                        <label class="text-xs text-gray-600">المفتاح (key)</label>
                        <input type="text" :name="'layers[' + index + '][key]'" x-model="layer.key" placeholder="title" class="mt-1 block w-full text-sm border-gray-300 rounded-md" required>
                    </div>
                    <div>
                        <label class="text-xs text-gray-600">التسمية (label)</label>
                        <input type="text" :name="'layers[' + index + '][label]'" x-model="layer.label" placeholder="العنوان الرئيسي" class="mt-1 block w-full text-sm border-gray-300 rounded-md" required>
                    </div>
                    <div>
                        <label class="text-xs text-gray-600">النوع</label>
                        <select :name="'layers[' + index + '][type]'" x-model="layer.type" class="mt-1 block w-full text-sm border-gray-300 rounded-md">
                            <option value="text">نص</option>
                            <option value="image">صورة</option>
                        </select>
                    </div>
                    <div class="flex items-end gap-2">
                        <input type="checkbox" :name="'layers[' + index + '][is_required]'" value="1" x-model="layer.is_required" class="rounded border-gray-300">
                        <label class="text-xs text-gray-600">حقل مطلوب</label>
                    </div>

                    <div>
                        <label class="text-xs text-gray-600">X</label>
                        <input type="number" :name="'layers[' + index + '][x]'" x-model="layer.x" class="mt-1 block w-full text-sm border-gray-300 rounded-md">
                    </div>
                    <div>
                        <label class="text-xs text-gray-600">Y</label>
                        <input type="number" :name="'layers[' + index + '][y]'" x-model="layer.y" class="mt-1 block w-full text-sm border-gray-300 rounded-md">
                    </div>
                    <div>
                        <label class="text-xs text-gray-600">العرض (width)</label>
                        <input type="number" :name="'layers[' + index + '][width]'" x-model="layer.width" class="mt-1 block w-full text-sm border-gray-300 rounded-md">
                    </div>
                    <div x-show="layer.type === 'image'">
                        <label class="text-xs text-gray-600">الارتفاع (height)</label>
                        <input type="number" :name="'layers[' + index + '][height]'" x-model="layer.height" class="mt-1 block w-full text-sm border-gray-300 rounded-md">
                    </div>

                    <template x-if="layer.type === 'text'">
                        <div class="contents">
                            <div>
                                <label class="text-xs text-gray-600">الخط</label>
                                <select :name="'layers[' + index + '][font_family]'" x-model="layer.font_family" class="mt-1 block w-full text-sm border-gray-300 rounded-md">
                                    <option value="Cairo">Cairo</option>
                                    <option value="Tajawal">Tajawal</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-xs text-gray-600">حجم الخط</label>
                                <input type="number" :name="'layers[' + index + '][font_size]'" x-model="layer.font_size" class="mt-1 block w-full text-sm border-gray-300 rounded-md">
                            </div>
                            <div>
                                <label class="text-xs text-gray-600">سُمك الخط</label>
                                <input type="number" step="100" :name="'layers[' + index + '][font_weight]'" x-model="layer.font_weight" class="mt-1 block w-full text-sm border-gray-300 rounded-md">
                            </div>
                            <div>
                                <label class="text-xs text-gray-600">اللون</label>
                                <input type="color" :name="'layers[' + index + '][color]'" x-model="layer.color" class="mt-1 block w-full h-9 border-gray-300 rounded-md">
                            </div>
                            <div>
                                <label class="text-xs text-gray-600">المحاذاة</label>
                                <select :name="'layers[' + index + '][align]'" x-model="layer.align" class="mt-1 block w-full text-sm border-gray-300 rounded-md">
                                    <option value="right">يمين</option>
                                    <option value="center">وسط</option>
                                    <option value="left">يسار</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-xs text-gray-600">الاتجاه</label>
                                <select :name="'layers[' + index + '][direction]'" x-model="layer.direction" class="mt-1 block w-full text-sm border-gray-300 rounded-md">
                                    <option value="rtl">RTL</option>
                                    <option value="ltr">LTR</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-xs text-gray-600">ارتفاع السطر</label>
                                <input type="number" step="0.1" :name="'layers[' + index + '][line_height]'" x-model="layer.line_height" class="mt-1 block w-full text-sm border-gray-300 rounded-md">
                            </div>
                            <div>
                                <label class="text-xs text-gray-600">تباعد الحروف</label>
                                <input type="number" step="0.1" :name="'layers[' + index + '][letter_spacing]'" x-model="layer.letter_spacing" class="mt-1 block w-full text-sm border-gray-300 rounded-md">
                            </div>
                            <div class="col-span-2">
                                <label class="text-xs text-gray-600">الظل (مثال: 2px 2px 4px #000)</label>
                                <input type="text" :name="'layers[' + index + '][text_shadow]'" x-model="layer.text_shadow" class="mt-1 block w-full text-sm border-gray-300 rounded-md">
                            </div>
                            <div class="col-span-2">
                                <label class="text-xs text-gray-600">نص افتراضي (placeholder)</label>
                                <input type="text" :name="'layers[' + index + '][placeholder]'" x-model="layer.placeholder" class="mt-1 block w-full text-sm border-gray-300 rounded-md">
                            </div>
                        </div>
                    </template>

                    <template x-if="layer.type === 'image'">
                        <div class="contents">
                            <div>
                                <label class="text-xs text-gray-600">استدارة الحواف</label>
                                <input type="number" :name="'layers[' + index + '][border_radius]'" x-model="layer.border_radius" class="mt-1 block w-full text-sm border-gray-300 rounded-md">
                            </div>
                            <div>
                                <label class="text-xs text-gray-600">طريقة العرض</label>
                                <select :name="'layers[' + index + '][object_fit]'" x-model="layer.object_fit" class="mt-1 block w-full text-sm border-gray-300 rounded-md">
                                    <option value="cover">Cover</option>
                                    <option value="contain">Contain</option>
                                    <option value="fill">Fill</option>
                                </select>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </template>

        <p x-show="layers.length === 0" class="text-sm text-gray-500">لا توجد طبقات. أضف طبقة نصية أو صورية لتفعيل النموذج الديناميكي عند الإنتاج.</p>
        <x-input-error :messages="$errors->get('layers')" class="mt-2" />
    </div>

    <div class="mt-6 flex justify-end">
        <x-primary-button>حفظ القالب</x-primary-button>
    </div>
</form>
