<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8">
    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-800 dark:text-white">{{ config('app.name') }}</h1>
        <p class="text-gray-500 mt-2">Login to your CRM / CRM में लॉगिन करें</p>
    </div>
    <form wire:submit="login" class="space-y-4">
        <div>
            <label class="block text-sm font-medium mb-1">Email</label>
            <input type="email" wire:model="email" class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:ring-2 focus:ring-indigo-500" placeholder="admin@demo.com">
            @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Password</label>
            <input type="password" wire:model="password" class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:ring-2 focus:ring-indigo-500" placeholder="••••••••">
        </div>
        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" wire:model="remember"> Remember me
        </label>
        <button type="submit" class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg transition">
            Login / लॉगिन
        </button>
    </form>
    <div class="mt-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg text-xs text-gray-600 dark:text-gray-300">
        <p class="font-semibold mb-2">Demo Credentials:</p>
        <p>Super Admin: admin@platform.com / Admin@123</p>
        <p>Tenant Admin: admin@demo.com / Demo@123</p>
    </div>
</div>
