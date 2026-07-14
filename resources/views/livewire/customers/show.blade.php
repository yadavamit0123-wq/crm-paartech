<div>
    @include('layouts.partials.leads-nav')
    <div class="mb-4">
        <a href="{{ route('leads.customers') }}" class="text-indigo-600 text-sm">← Back to Customers</a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border dark:border-gray-700 mb-6">
        <div class="flex flex-wrap justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold">{{ $customer->name }}</h1>
                <p class="text-gray-500">{{ $customer->company }} • {{ $customer->phone }} • {{ $customer->email }}</p>
                @if($customer->gstin)<p class="text-sm text-gray-400">GSTIN: {{ $customer->gstin }}</p>@endif
            </div>
            <div class="flex gap-2">
                @if(auth()->user()->hasPermission('documents.create'))
                <a href="{{ route('leads.documents.create', ['customer_id' => $customer->id]) }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm">+ Create Invoice</a>
                @endif
                @if(auth()->user()->hasPermission('payments.manage'))
                <button wire:click="$set('showPlanForm', true)" class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm">💰 Payment Plan</button>
                @endif
            </div>
        </div>
    </div>

    @if($showPlanForm)
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border dark:border-gray-700 mb-6">
        <h3 class="font-semibold mb-4">Create Milestone Payment Plan / भुगतान योजना</h3>
        <div class="grid md:grid-cols-2 gap-4 mb-4">
            <input type="text" wire:model="planTitle" placeholder="Plan title (e.g. Website Development)" class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
            <input type="number" wire:model="planTotal" step="0.01" placeholder="Total amount ₹" class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
            <textarea wire:model="planDescription" rows="2" placeholder="Description" class="md:col-span-2 px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600"></textarea>
        </div>

        <h4 class="text-sm font-medium mb-2">Milestones (must total 100%)</h4>
        @foreach($milestones as $idx => $m)
        <div class="grid md:grid-cols-4 gap-2 mb-2">
            <input type="text" wire:model="milestones.{{ $idx }}.name" placeholder="Name" class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 text-sm">
            <input type="number" wire:model="milestones.{{ $idx }}.percentage" step="0.01" placeholder="%" class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 text-sm">
            <input type="text" wire:model="milestones.{{ $idx }}.trigger_event" placeholder="Trigger event" class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 text-sm">
            @if($planTotal && ($m['percentage'] ?? 0) > 0)
            <div class="flex items-center text-sm text-gray-500">₹{{ number_format($planTotal * ($m['percentage']/100), 2) }}</div>
            @endif
        </div>
        @endforeach
        <button type="button" wire:click="addMilestone" class="text-sm text-indigo-600 mb-4">+ Add Milestone</button>

        <div class="flex gap-2">
            <button wire:click="createPaymentPlan" class="px-4 py-2 bg-green-600 text-white rounded-lg">Create Plan</button>
            <button wire:click="$set('showPlanForm', false)" class="px-4 py-2 border rounded-lg">Cancel</button>
        </div>
    </div>
    @endif

    @foreach($customer->paymentPlans as $plan)
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border dark:border-gray-700 mb-6">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h3 class="font-semibold">{{ $plan->title }}</h3>
                <p class="text-sm text-gray-500">Total: ₹{{ number_format($plan->total_amount, 2) }} | Paid: ₹{{ number_format($plan->paidAmount(), 2) }} | Pending: ₹{{ number_format($plan->pendingAmount(), 2) }}</p>
            </div>
            <span class="px-3 py-1 rounded-full text-xs {{ $plan->status === 'completed' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">{{ ucfirst($plan->status) }}</span>
        </div>

        <div class="space-y-4">
            @foreach($plan->milestones as $milestone)
            <div class="border dark:border-gray-700 rounded-lg p-4">
                <div class="flex flex-wrap justify-between items-start gap-3">
                    <div>
                        <div class="font-medium">{{ $milestone->name }} — {{ $milestone->percentage }}% (₹{{ number_format($milestone->amount, 2) }})</div>
                        <div class="text-xs text-gray-500">{{ $milestone->trigger_event }}</div>
                        <span class="inline-block mt-1 px-2 py-0.5 rounded text-xs
                            @if($milestone->status === 'paid') bg-green-100 text-green-700
                            @elseif($milestone->status === 'link_sent') bg-blue-100 text-blue-700
                            @elseif($milestone->status === 'approved') bg-indigo-100 text-indigo-700
                            @else bg-gray-100 text-gray-600 @endif">
                            {{ ucfirst(str_replace('_', ' ', $milestone->status)) }}
                        </span>
                    </div>
                    @if(auth()->user()->hasPermission('payments.manage') && $milestone->status !== 'paid')
                    <div class="flex flex-wrap gap-2">
                        @if($milestone->status === 'pending')
                        <button wire:click="approveMilestone({{ $milestone->id }})" class="px-3 py-1 bg-indigo-600 text-white rounded text-xs">✓ Approve</button>
                        @endif
                        @if(in_array($milestone->status, ['approved', 'link_sent']))
                        <button wire:click="generateLink({{ $milestone->id }})" class="px-3 py-1 bg-green-600 text-white rounded text-xs">🔗 Generate Link + QR</button>
                        @endif
                        <button wire:click="recordPayment({{ $milestone->id }}, 'cash')" wire:confirm="Mark as paid (Cash)?" class="px-3 py-1 bg-yellow-600 text-white rounded text-xs">💵 Cash</button>
                        <button wire:click="recordPayment({{ $milestone->id }}, 'bank')" wire:confirm="Mark as paid (Bank)?" class="px-3 py-1 bg-purple-600 text-white rounded text-xs">🏦 Bank</button>
                        <button wire:click="recordPayment({{ $milestone->id }}, 'upi')" wire:confirm="Mark as paid (UPI)?" class="px-3 py-1 bg-teal-600 text-white rounded text-xs">📱 UPI</button>
                    </div>
                    @endif
                </div>

                @if($milestone->payment_link)
                <div class="mt-4 grid md:grid-cols-2 gap-4 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                    <div>
                        <div class="text-xs text-gray-500 mb-1">Payment Link</div>
                        <a href="{{ $milestone->payment_link }}" target="_blank" class="text-indigo-600 text-sm break-all">{{ $milestone->payment_link }}</a>
                        @if($customer->phone)
                        <a href="https://wa.me/91{{ preg_replace('/[^0-9]/','',$customer->phone) }}?text={{ urlencode('Payment link: '.$milestone->payment_link) }}" target="_blank" class="block mt-2 text-xs text-green-600">Send via WhatsApp →</a>
                        @endif
                    </div>
                    @if($milestone->payment_qr_url)
                    <div class="text-center">
                        <img src="{{ $milestone->payment_qr_url }}" alt="Payment QR" class="mx-auto w-32 h-32 border rounded">
                        <div class="text-xs text-gray-500 mt-1">Scan to Pay</div>
                    </div>
                    @endif
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endforeach

    @if($documents->count())
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border dark:border-gray-700 mb-6">
        <h3 class="font-semibold mb-3">Documents</h3>
        @foreach($documents as $doc)
        <div class="flex justify-between py-2 border-b dark:border-gray-700 last:border-0 text-sm">
            <span>{{ $doc->document_number }} — {{ $doc->typeLabel() }}</span>
            <span>₹{{ number_format($doc->grand_total, 2) }}</span>
        </div>
        @endforeach
    </div>
    @endif
</div>
