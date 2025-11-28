<x-layouts.app>
    @if(!$company)
        <div class="py-10 text-center text-sm text-gray-500">
            No company found yet. Once Shopify install is wired, this page will show your store stats.
        </div>
    @else
        <div class="mb-6">
            <h1 class="text-2xl font-semibold mb-1">
                {{ $company->name }} – WhatsApp Automation
            </h1>
            <p class="text-xs text-gray-500">
                Domain: {{ $company->shopify_domain }}
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-lg border p-4 shadow-sm">
                <div class="text-xs text-gray-500 mb-1">COD Orders</div>
                <div class="text-2xl font-bold">
                    {{ $company->orders()->where('payment_method','COD')->count() }}
                </div>
            </div>
            <div class="bg-white rounded-lg border p-4 shadow-sm">
                <div class="text-xs text-gray-500 mb-1">Abandoned Carts</div>
                <div class="text-2xl font-bold">
                    {{ $company->abandonedCheckouts()->count() }}
                </div>
            </div>
            <div class="bg-white rounded-lg border p-4 shadow-sm">
                <div class="text-xs text-gray-500 mb-1">Active Automations</div>
                <div class="text-2xl font-bold">
                    {{ $company->automations()->where('is_active', true)->count() }}
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg border p-4 shadow-sm">
            <div class="flex justify-between items-center mb-3">
                <h2 class="text-sm font-semibold">Recent orders (demo)</h2>
                <span class="text-[11px] text-gray-400">
                    When webhooks are added, this will show live Shopify data.
                </span>
            </div>
            <table class="w-full text-xs">
                <thead>
                    <tr class="border-b text-gray-500">
                        <th class="text-left py-2">Order</th>
                        <th class="text-left py-2">Customer</th>
                        <th class="text-left py-2">COD</th>
                        <th class="text-left py-2">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($company->orders()->latest()->take(5)->get() as $order)
                        <tr class="border-b last:border-0">
                            <td class="py-2">#{{ $order->shopify_order_id }}</td>
                            <td class="py-2">{{ $order->customer_phone ?: $order->customer_email }}</td>
                            <td class="py-2">
                                @if($order->payment_method === 'COD')
                                    <span class="px-2 py-0.5 rounded-full text-[11px]
                                        @if($order->cod_status === 'confirmed') bg-green-100 text-green-700
                                        @elseif($order->cod_status === 'cancelled') bg-red-100 text-red-700
                                        @else bg-yellow-100 text-yellow-700 @endif">
                                        {{ ucfirst($order->cod_status) }}
                                    </span>
                                @else
                                    <span class="text-[11px] text-gray-400">Prepaid</span>
                                @endif
                            </td>
                            <td class="py-2">₹{{ number_format($order->total_price, 2) }}</td>
                        </tr>
                    @endforeach
                    @if($company->orders()->count() === 0)
                        <tr>
                            <td colspan="4" class="py-4 text-center text-gray-400 text-xs">
                                No demo orders yet.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    @endif
</x-layouts.app>
