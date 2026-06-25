<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">معرض التصاميم</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-md">{{ session('status') }}</div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                @if ($designs->isEmpty())
                    <p class="text-gray-500 text-center py-10">لا توجد تصاميم منتجة حتى الآن.</p>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        @foreach ($designs as $design)
                            <div class="border rounded-lg overflow-hidden">
                                @if ($design->status === 'completed' && $design->output_url)
                                    <img src="{{ $design->output_url }}" class="w-full h-48 object-cover bg-gray-100" alt="تصميم">
                                @else
                                    <div class="w-full h-48 flex items-center justify-center bg-gray-100 text-sm text-gray-500">
                                        {{ $design->status === 'failed' ? 'فشل الإنتاج' : 'قيد المعالجة' }}
                                    </div>
                                @endif
                                <div class="p-3">
                                    <p class="text-sm text-gray-600 mb-2">{{ $design->template->name ?? 'قالب محذوف' }}</p>
                                    <div class="flex gap-2">
                                        @if ($design->output_url)
                                            <a href="{{ $design->output_url }}" download class="text-sm text-indigo-600 hover:underline">تنزيل</a>
                                        @endif
                                        <form action="{{ route('designs.destroy', $design) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من الحذف؟')">
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
                        {{ $designs->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
