<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ config('app.name', 'Whatify SaaS') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <!-- AlpineJS + SortableJS for builder -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script> {{-- [web:110] --}}
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script> {{-- [web:31] --}}
</head>

<body class="bg-gray-100 text-gray-900 antialiased">
    
    <div class="min-h-screen flex flex-col">
<header class="bg-white border-b">
    <div class="max-w-6xl mx-auto px-4 py-3 flex justify-between items-center">
        <div class="font-semibold text-lg">Whatify Shopify SaaS</div>
        @php
            $currentCompany = \App\Models\Company::first();
        @endphp
        <nav class="text-sm space-x-4">
            <a href="{{ url('/') }}" class="text-gray-600 hover:text-gray-900">Marketing</a>
            <a href="{{ url('/app') }}" class="text-gray-600 hover:text-gray-900">App</a>
            @if($currentCompany)
                <a href="{{ route('whatsapp.settings.edit', $currentCompany) }}" class="text-gray-600 hover:text-gray-900">WhatsApp Settings</a>
                <a href="{{ route('automations.index', $currentCompany) }}" class="text-gray-600 hover:text-gray-900">Automations</a>
            @endif
            <a href="{{ url('/dev/create-demo-cod') }}" class="text-gray-400 hover:text-gray-700">Dev: Create COD</a>
            <a href="{{ url('/dev/run-cod-automation') }}" class="text-gray-400 hover:text-gray-700">Dev: Run COD</a>
            <a href="{{ url('/dev/create-demo-abandoned-cart') }}" class="text-gray-400 hover:text-gray-700">Dev: Create Cart</a>
            <a href="{{ url('/dev/run-abandoned-cart-automation') }}" class="text-gray-400 hover:text-gray-700">Dev: Run Cart</a>
        </nav>
    </div>
</header>



        <main class="flex-1 max-w-6xl mx-auto px-4 py-6">
            {{ $slot }}
        </main>
    </div>
    @livewireScripts
</body>
</html>
