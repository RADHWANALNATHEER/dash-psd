<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">استيراد قوالب من ملفات PSD</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-100 text-red-800 rounded-md">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <p class="text-sm text-gray-600 mb-2">
                    اختر ملف أو أكثر بصيغة PSD. كل Artboard داخل الملف سيتحول تلقائيًا إلى قالب مستقل،
                    وستُستخرج طبقات النص كحقول ديناميكية تلقائيًا.
                </p>
                <p class="text-sm text-gray-500 mb-6">
                    لتحويل طبقة صورة إلى حقل ديناميكي (مثل صورة الخبر)، أضف كلمة
                    <code class="bg-gray-100 px-1 rounded">image</code> أو <code class="bg-gray-100 px-1 rounded">صورة</code>
                    إلى اسم الطبقة داخل فوتوشوب قبل الاستيراد، وإلا ستبقى جزءًا من خلفية القالب.
                </p>

                <form method="POST" action="{{ route('templates.import.store') }}" enctype="multipart/form-data"
                      x-data="{ files: [] }">
                    @csrf

                    <div class="mb-4">
                        <x-input-label for="psd_files" value="ملفات PSD" />
                        <input type="file" id="psd_files" name="psd_files[]" accept=".psd" multiple required
                               @change="files = Array.from($event.target.files)"
                               class="mt-1 block w-full text-sm">
                        <template x-if="files.length">
                            <ul class="mt-2 text-sm text-gray-500 list-disc list-inside">
                                <template x-for="file in files" :key="file.name">
                                    <li x-text="file.name"></li>
                                </template>
                            </ul>
                        </template>
                    </div>

                    <div class="mb-6">
                        <x-input-label for="description" value="وصف اختياري للقوالب المستوردة" />
                        <textarea id="description" name="description" rows="2"
                                  class="mt-1 block w-full border-gray-300 rounded-md" dir="rtl"></textarea>
                    </div>

                    <div class="flex justify-end gap-3">
                        <a href="{{ route('templates.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:underline">إلغاء</a>
                        <x-primary-button>استيراد القوالب</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
