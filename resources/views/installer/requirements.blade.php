@extends('installer.layout')

@section('content')
<h2 class="text-xl font-semibold mb-4 text-gray-800">Server Requirements</h2>
<p class="text-sm text-gray-500 mb-6">Please ensure your server meets these requirements before continuing.</p>

<div class="border rounded-lg overflow-hidden mb-8 shadow-sm">
    <table class="w-full text-left text-sm text-gray-700">
        <tbody class="divide-y divide-gray-100">
            @php
            $descriptions = [
                'php' => 'Requires PHP version ≥ 8.2.',
                'bcmath' => 'Arbitrary precision mathematics. Enable `extension=bcmath` in php.ini.',
                'ctype' => 'Character type checking. Usually enabled by default in PHP.',
                'fileinfo' => 'File type identification. Remove the semicolon from `;extension=fileinfo` in php.ini.',
                'json' => 'JSON data support. Enable `extension=json` in php.ini.',
                'mbstring' => 'Multibyte string support. Enable `extension=mbstring` in php.ini.',
                'openssl' => 'Secure data encryption. Enable `extension=openssl` in php.ini.',
                'pdo' => 'Database connection interface. Enable `extension=pdo` in php.ini.',
                'tokenizer' => 'Source code analysis string tokenizer. Enabled by default.',
                'xml' => 'XML file parsing. Enable XML extensions in php.ini.',
                'storage' => 'The `storage` directory must have write permissions (755/777).',
                'bootstrap' => 'The `bootstrap/cache` directory must have write permissions (755/777).'
            ];
            @endphp
            @foreach($requirements as $req => $passed)
            <tr class="transition-colors hover:bg-gray-50">
                <td class="px-4 py-3">
                    <div class="font-medium uppercase text-gray-700">{{ $req }}</div>
                    @if(isset($descriptions[$req]))
                        <div class="text-xs text-gray-500 mt-1 {{ !$passed ? 'text-red-500' : '' }}">{{ $descriptions[$req] }}</div>
                    @endif
                </td>
                <td class="px-4 py-3 text-right">
                    @if($passed)
                        <span class="inline-flex items-center gap-1 text-emerald-600 font-semibold">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Passed
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 text-red-500 font-semibold">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            Failed
                        </span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="flex justify-between items-center mt-6">
    <div class="text-sm text-gray-500">Step 1 of 4</div>
    @if($pass)
        <a href="{{ route('install.database') }}" class="px-6 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-medium transition-colors inline-flex items-center gap-2">
            Continue
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
        </a>
    @else
        <button disabled class="px-6 py-2.5 bg-gray-300 text-gray-500 rounded-lg cursor-not-allowed font-medium inline-flex items-center gap-2">
            Fix errors to continue
        </button>
    @endif
</div>
@endsection
