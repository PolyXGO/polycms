@extends('installer.layout')

@section('content')
<h2 class="text-xl font-semibold mb-4 text-gray-800">Database Migrations</h2>
<p class="text-sm text-gray-500 mb-6">We are now ready to build your database tables and seed initial data. This may take a minute.</p>

<div class="py-8 text-center" id="status-container">
    <div id="loading" class="flex flex-col items-center justify-center">
        <svg class="animate-spin h-10 w-10 text-indigo-600 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <p class="text-gray-900 font-semibold mb-4">Building System Architecture. Please wait...</p>
        
        <div class="text-left w-full max-w-sm bg-gray-50 p-4 rounded-lg border border-gray-100 shadow-sm relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-1 bg-indigo-100 overflow-hidden">
                <div class="h-full bg-indigo-500 animate-pulse w-full"></div>
            </div>
            <ul class="space-y-2 text-sm text-gray-600 font-medium pl-2 border-l-2 border-indigo-200">
                <li class="pl-2">Migrating Core Database Tables</li>
                <li class="pl-2">Registering FlexiMyTa Theme</li>
                <li class="pl-2">Registering FlexiWhite Theme</li>
                <li class="pl-2">Registering FlexiDocs Theme</li>
                <li class="pl-2">Installing CookieConsent Module</li>
                <li class="pl-2">Installing Google2FA Module</li>
                <li class="pl-2">Installing PaypalGateway Module</li>
                <li class="pl-2">Installing BannerSlider Module</li>
            </ul>
        </div>
    </div>

    <div id="error-alert" class="hidden bg-red-50 text-red-600 p-4 rounded-lg mt-4 text-sm border border-red-100 text-left">
        <strong>Error:</strong> <span id="error-text"></span>
    </div>
</div>

<div class="flex justify-between items-center mt-6">
    <div class="text-sm text-gray-500">Step 3 of 4</div>
    <a href="{{ route('install.admin') }}" id="next-btn" class="hidden px-6 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-medium transition-colors items-center gap-2">
        Continue to Admin Setup
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
    </a>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log("Running migrations...");
        fetch('{{ route('install.migrations.run') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('loading').classList.add('hidden');
            if(data.status === 'success') {
                document.getElementById('status-container').innerHTML = `
                    <div class="flex flex-col items-center justify-center text-emerald-600">
                        <svg class="w-12 h-12 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <p class="font-medium text-lg">Database successfully built!</p>
                    </div>
                `;
                let btn = document.getElementById('next-btn');
                btn.classList.remove('hidden');
                btn.classList.add('inline-flex');
            } else {
                document.getElementById('error-alert').classList.remove('hidden');
                document.getElementById('error-text').innerText = data.message;
                document.getElementById('loading').innerHTML = '<p class="text-red-500 font-medium">Migration Failed.</p>';
            }
        })
        .catch(error => {
            document.getElementById('loading').classList.add('hidden');
            document.getElementById('error-alert').classList.remove('hidden');
            document.getElementById('error-text').innerText = 'Server error occurred.';
        });
    });
</script>
@endsection
