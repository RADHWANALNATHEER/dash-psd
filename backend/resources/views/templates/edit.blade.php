<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">تعديل القالب: {{ $template->name }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                @include('templates._form', ['template' => $template, 'action' => route('templates.update', $template), 'method' => 'PUT'])
            </div>
        </div>
    </div>
</x-app-layout>
