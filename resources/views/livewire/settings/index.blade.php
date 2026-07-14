<div>
    @include('layouts.partials.leads-nav')
    <h1 class="text-2xl font-bold mb-6">Organisation Settings</h1>
    <div class="grid lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border dark:border-gray-700 space-y-4">
            <h3 class="font-semibold">Company Profile</h3>
            <input wire:model="orgName" placeholder="Organisation name" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
            <div class="grid grid-cols-2 gap-2">
                <input wire:model="email" placeholder="Email" class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                <input wire:model="phone" placeholder="Phone" class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
            </div>
            <input wire:model="gstin" placeholder="GSTIN" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
            <textarea wire:model="address" rows="2" placeholder="Address" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600"></textarea>
            <div class="grid grid-cols-2 gap-2">
                <input wire:model="city" placeholder="City" class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                <input wire:model="state" placeholder="State" class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
            </div>
        </div>
        <div class="space-y-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border dark:border-gray-700 space-y-3">
                <h3 class="font-semibold">CRM Controls</h3>
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" wire:model="duplicateCheck"> Duplicate lead check</label>
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" wire:model="callLogRestrict"> Restrict call log visibility</label>
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" wire:model="leadEditLock"> Lead edit lock after assign</label>
                <input wire:model="leadPrefix" placeholder="Lead ID prefix" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border dark:border-gray-700 space-y-3">
                <h3 class="font-semibold">WhatsApp Settings</h3>
                <input wire:model="whatsappNumber" placeholder="WhatsApp Business Number" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                <div class="space-y-2">
                    @foreach($whatsappTemplates as $i => $tpl)
                    <div class="flex gap-2 text-sm"><span class="flex-1 p-2 bg-gray-50 dark:bg-gray-700 rounded">{{ $tpl }}</span><button wire:click="removeTemplate({{ $i }})" class="text-red-600">×</button></div>
                    @endforeach
                    <div class="flex gap-2"><input wire:model="newTemplate" placeholder="Add template" class="flex-1 px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 text-sm"><button wire:click="addTemplate" class="px-3 py-2 bg-indigo-600 text-white rounded-lg text-sm">Add</button></div>
                </div>
            </div>
        </div>
    </div>
    <button wire:click="save" class="mt-6 px-6 py-2 bg-indigo-600 text-white rounded-lg">Save Settings</button>
</div>
