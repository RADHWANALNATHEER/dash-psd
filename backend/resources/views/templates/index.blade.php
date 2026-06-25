<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">القوالب</h2>
            <a href="{{ route('templates.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                + قالب جديد
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-md">{{ session('status') }}</div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                @if ($templates->isEmpty())
                    <p class="text-gray-500 text-center py-10">لا توجد قوالب حتى الآن. ابدأ بإضافة قالب جديد.</p>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach ($templates as $template)
                            <div class="border rounded-lg overflow-hidden">
                                <img src="{{ $template->background_url }}"
                                     alt="{{ $template->name }}" class="w-full h-48 object-cover bg-gray-100">
                                <div class="p-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <h3 class="font-bold text-gray-800">{{ $template->name }}</h3>
                                        @if (! $template->is_active)
                                            <span class="text-xs bg-gray-200 text-gray-600 px-2 py-1 rounded">معطّل</span>
                                        @endif
                                    </div>
                                    <p class="text-sm text-gray-500 mb-3">{{ $template->width }}×{{ $template->height }} · {{ $template->layers->count() }} طبقة</p>
                                    <div class="flex gap-2">
                                        <a href="{{ route('designs.create', ['template_id' => $template->id]) }}" class="text-sm text-indigo-600 hover:underline">إنتاج تصميم</a>
                                        <a href="{{ route('templates.edit', $template) }}" class="text-sm text-gray-600 hover:underline">تعديل</a>
                                        <form action="{{ route('templates.destroy', $template) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من الحذف؟')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-sm text-red-600 hover:underline">حذف</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-6">
                        {{ $templates->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
