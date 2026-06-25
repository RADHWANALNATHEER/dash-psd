<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            الرئيسية
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <a href="{{ route('templates.index') }}" class="bg-white shadow-sm rounded-lg p-6 hover:shadow-md transition">
                    <h3 class="font-bold text-gray-800 mb-2">القوالب</h3>
                    <p class="text-sm text-gray-500">إدارة وإضافة قوالب التصميم وطبقاتها.</p>
                </a>
                <a href="{{ route('designs.create') }}" class="bg-white shadow-sm rounded-lg p-6 hover:shadow-md transition">
                    <h3 class="font-bold text-gray-800 mb-2">إنتاج تصميم</h3>
                    <p class="text-sm text-gray-500">اختر قالبًا وأدخل النصوص والصور لإنتاج تصميمك.</p>
                </a>
                <a href="{{ route('designs.gallery') }}" class="bg-white shadow-sm rounded-lg p-6 hover:shadow-md transition">
                    <h3 class="font-bold text-gray-800 mb-2">المعرض</h3>
                    <p class="text-sm text-gray-500">تصفح وتنزيل التصاميم المُنتَجة سابقًا.</p>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
