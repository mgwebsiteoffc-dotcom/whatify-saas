<x-layouts.app>
    <div class="py-16 text-center">
        <h1 class="text-4xl font-bold mb-4">
            WhatsApp Automation for Shopify COD & Abandoned Carts
        </h1>
        <p class="text-gray-600 max-w-2xl mx-auto mb-8">
            Turn WhatsApp into your highest‑converting sales channel with COD confirmation and abandoned cart flows powered by Whatify API.
        </p>

        <div class="flex justify-center gap-4 mb-10">
            <a href="{{ url('/app') }}"
               class="px-5 py-3 rounded-md bg-green-600 text-white text-sm font-semibold hover:bg-green-700">
                Open app
            </a>
            <a href="#features"
               class="px-5 py-3 rounded-md border border-gray-300 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                View features
            </a>
        </div>

        <div id="features" class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8 text-left">
            <div class="bg-white border rounded-lg p-5 shadow-sm">
                <h2 class="text-sm font-semibold mb-2">COD Order Confirmation</h2>
                <p class="text-xs text-gray-600">
                    Auto‑send WhatsApp templates when a COD order is created to confirm or cancel before shipping.
                </p>
            </div>
            <div class="bg-white border rounded-lg p-5 shadow-sm">
                <h2 class="text-sm font-semibold mb-2">Abandoned Cart Recovery</h2>
                <p class="text-xs text-gray-600">
                    Recover lost revenue with 3‑step WhatsApp sequences (15 min → 2 h → 24 h) using Whatify template messages.
                </p>
            </div>
            <div class="bg-white border rounded-lg p-5 shadow-sm">
                <h2 class="text-sm font-semibold mb-2">Drag‑and‑Drop Flows</h2>
                <p class="text-xs text-gray-600">
                    Build flows visually with text, media, buttons, delay, and task steps, similar to Shopify Flow.
                </p>
            </div>
        </div>
    </div>
</x-layouts.app>
