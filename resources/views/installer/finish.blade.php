@extends('installer.layout')

@section('content')
<div class="text-center py-8">
    <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-emerald-100 text-emerald-600 mb-6 border-4 border-emerald-50">
        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
    </div>
    
    <h2 class="text-2xl font-bold mb-2 text-gray-800">Installation Complete!</h2>
    <p class="text-gray-500 mb-8 max-w-sm mx-auto">PolyCMS has been successfully installed and configured. You can now login to your admin dashboard.</p>

    <div class="bg-gray-50 p-4 rounded-lg mb-8 text-sm text-gray-700 border border-gray-100 text-left w-full max-w-sm mx-auto">
        <p class="mb-2"><span class="font-semibold">Security Note:</span></p>
        <p>The system has automatically created an <code class="bg-gray-200 px-1 py-0.5 rounded">installed.lock</code> file to prevent unauthorized reinstalls.</p>
    </div>

    <a href="{{ url('/admin/login') }}" class="px-8 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-medium transition-colors shadow-sm inline-block">
        Go to Login Page
    </a>
</div>
@endsection
