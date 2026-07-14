<div class="bg-white rounded-2xl shadow-2xl p-8">
    <div class="text-center mb-8">
        <div class="w-12 h-12 mx-auto mb-4 rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center text-white font-bold text-lg shadow-lg">
            {{ strtoupper(substr(config('app.name'), 0, 2)) }}
        </div>
        <h1 class="text-2xl font-bold text-gray-900">{{ config('app.name') }}</h1>
        <p class="text-gray-500 text-sm mt-1.5">Sign in to your workspace / CRM में लॉगिन करें</p>
    </div>
    <form wire:submit="login" class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
            <input type="email" wire:model="email" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 text-gray-900 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition" placeholder="you@company.com">
            @error('email') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
            <input type="password" wire:model="password" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 text-gray-900 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition" placeholder="••••••••">
        </div>
        <label class="flex items-center gap-2 text-sm text-gray-600">
            <input type="checkbox" wire:model="remember" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"> Remember me
        </label>
        <button type="submit" wire:loading.attr="disabled" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg transition shadow-lg shadow-indigo-600/25 disabled:opacity-60">
            <span wire:loading.remove wire:target="login">Sign In / लॉगिन</span>
            <span wire:loading wire:target="login">Signing in...</span>
        </button>
    </form>
    <div class="mt-6 p-4 bg-slate-50 border border-slate-200 rounded-lg text-xs text-gray-600">
        <p class="font-semibold mb-1.5 text-gray-700">Demo Credentials</p>
        <p>Super Admin: admin@platform.com / Admin@123</p>
        <p>Tenant Admin: admin@demo.com / Demo@123</p>
    </div>
</div>
