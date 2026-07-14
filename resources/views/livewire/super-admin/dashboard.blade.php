<div>
    <h1 class="text-2xl font-bold mb-6">Super Admin Dashboard</h1>

    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border">
            <div class="text-3xl font-bold">{{ $stats['tenants'] }}</div>
            <div class="text-gray-500 text-sm">Total Tenants</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border">
            <div class="text-3xl font-bold text-green-500">{{ $stats['active_tenants'] }}</div>
            <div class="text-gray-500 text-sm">Active Tenants</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border">
            <div class="text-3xl font-bold">{{ $stats['total_users'] }}</div>
            <div class="text-gray-500 text-sm">Total Users</div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border overflow-hidden">
        <div class="p-4 border-b font-semibold">All Tenants</div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="px-4 py-3 text-left">Company</th>
                    <th class="px-4 py-3 text-left">Subdomain</th>
                    <th class="px-4 py-3 text-left">Plan</th>
                    <th class="px-4 py-3 text-left">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y dark:divide-gray-700">
                @foreach($tenants as $tenant)
                <tr>
                    <td class="px-4 py-3 font-medium">{{ $tenant->name }}</td>
                    <td class="px-4 py-3">{{ $tenant->subdomain }}.{{ config('app.platform_domain') }}</td>
                    <td class="px-4 py-3">{{ $tenant->subscription?->plan?->name ?? '-' }}</td>
                    <td class="px-4 py-3"><span class="{{ $tenant->is_active ? 'text-green-500' : 'text-red-500' }}">{{ $tenant->is_active ? 'Active' : 'Inactive' }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="p-4">{{ $tenants->links() }}</div>
    </div>
</div>
