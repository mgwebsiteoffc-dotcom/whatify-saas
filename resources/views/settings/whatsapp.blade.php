<x-layouts.app>
    <div class="max-w-xl mx-auto bg-white border rounded-lg shadow-sm p-6">
        <h1 class="text-lg font-semibold mb-4">WhatsApp / Whatify settings</h1>

        @if(session('message'))
            <div class="mb-3 text-xs text-green-700 bg-green-50 border border-green-200 rounded px-3 py-2">
                {{ session('message') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-3 text-xs text-red-700 bg-red-50 border border-red-200 rounded px-3 py-2">
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('whatsapp.settings.update', $company) }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Whatify API token</label>
                <input type="text"
                       name="whatify_token"
                       value="{{ old('whatify_token', $company->whatify_token) }}"
                       class="w-full border-gray-300 rounded-md text-sm">
                @error('whatify_token')
                    <p class="text-[11px] text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Default business phone (E.164)</label>
                <input type="text"
                       name="business_phone"
                       value="{{ old('business_phone', $company->business_phone) }}"
                       class="w-full border-gray-300 rounded-md text-sm"
                       placeholder="+91XXXXXXXXXX">
                @error('business_phone')
                    <p class="text-[11px] text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

        <div class="flex justify-between items-center pt-2">
            <button type="submit"
                    class="px-4 py-2 rounded-md bg-green-600 text-white text-xs font-semibold hover:bg-green-700">
                Save settings
            </button>
        </div>
        </form>  <!-- CLOSE main form here -->

        <form method="POST" action="{{ route('whatsapp.settings.test', $company) }}" class="flex items-center gap-2 mt-4">
            @csrf
            <input type="text"
                   name="test_phone"
                   class="border-gray-300 rounded-md text-xs"
                   placeholder="E.164 like 919569283474">
            <button type="submit"
                    class="px-3 py-1.5 rounded-md bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-700">
                Send test
            </button>
        </form>

            </div>
        </form>
    </div>
</x-layouts.app>
