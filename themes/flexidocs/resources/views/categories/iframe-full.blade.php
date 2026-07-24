@extends('theme.flexidocs::layouts.wiki')

@section('hide_sidebar', true)
@section('wrapper_class', 'h-full w-full')

@section('content')
    <div class="h-full w-full flex flex-col">
        @php
            $iframeUrl = $category->meta['iframe_url'] ?? '';
        @endphp
        
        @if($iframeUrl)
            <iframe 
                src="{{ $iframeUrl }}" 
                title="{{ $category->name ?? 'Category' }}"
                class="w-full h-full border-0 min-h-[calc(100vh-64px)]"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                allowfullscreen
                loading="lazy"
            ></iframe>
        @else
            <div class="flex items-center justify-center p-12 text-gray-500 dark:text-gray-400 text-center w-full min-h-[50vh]">
                <div class="space-y-4">
                    <svg class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <p class="text-lg font-medium">{{ _l('Chưa cấu hình URL Iframe') }}</p>
                    <p class="text-sm mt-2">{{ _l('Vui lòng cập nhật trường Iframe URL trong phần chỉnh sửa danh mục.') }}</p>
                </div>
            </div>
        @endif
    </div>
@endsection
