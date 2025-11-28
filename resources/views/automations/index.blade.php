<x-layouts.app>
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-lg font-semibold">WhatsApp automations</h1>
        <a href="{{ route('automations.create', $company) }}"
           class="px-3 py-1.5 rounded-md bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-700">
            + New automation
        </a>
    </div>

    @if(session('message'))
        <div class="mb-3 text-xs text-green-700 bg-green-50 border border-green-200 rounded px-3 py-2">
            {{ session('message') }}
        </div>
    @endif

    <div class="bg-white border rounded-lg shadow-sm">
        <table class="w-full text-xs">
            <thead>
                <tr class="border-b text-gray-500">
                    <th class="text-left py-2 px-3">Name</th>
                    <th class="text-left py-2 px-3">Trigger</th>
                    <th class="text-left py-2 px-3">Active</th>
                    <th class="text-left py-2 px-3">Created</th>
                </tr>
            </thead>
            <tbody>
                @forelse($automations as $automation)
                    <tr class="border-b last:border-0">
                       <td class="py-2 px-3">
    <a href="{{ route('automations.edit', [$company, $automation]) }}"
       class="text-indigo-600 hover:underline">
        {{ $automation->name }}
    </a>
</td>
                        <td class="py-2 px-3">{{ $automation->trigger }}</td>
                        <td class="py-2 px-3">
                            @if($automation->is_active)
                                <span class="px-2 py-0.5 rounded-full text-[11px] bg-green-100 text-green-700">Active</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[11px] bg-gray-100 text-gray-600">Inactive</span>
                            @endif
                        </td>
                        <td class="py-2 px-3 text-gray-500">
                            {{ $automation->created_at->format('Y-m-d H:i') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="py-4 text-center text-gray-400 text-xs">
                            No automations yet. Create your first COD or abandoned cart flow.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-layouts.app>
