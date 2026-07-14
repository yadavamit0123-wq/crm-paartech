<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - {{ $milestone->plan->title }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl p-8 max-w-md w-full text-center">
        <div class="text-sm text-gray-500 mb-2">{{ $milestone->plan->tenant->name }}</div>
        <h1 class="text-2xl font-bold mb-1">{{ $milestone->name }}</h1>
        <p class="text-gray-500 text-sm mb-6">{{ $milestone->plan->title }}</p>

        <div class="text-4xl font-bold text-indigo-600 mb-2">₹{{ number_format($milestone->amount, 2) }}</div>
        <div class="text-sm text-gray-500 mb-6">{{ $milestone->percentage }}% of ₹{{ number_format($milestone->plan->total_amount, 2) }}</div>

        @if($milestone->status === 'paid')
        <div class="bg-green-100 text-green-800 px-4 py-3 rounded-lg font-semibold">✅ Payment Completed</div>
        @else
            @if($milestone->payment_qr_url)
            <img src="{{ $milestone->payment_qr_url }}" alt="QR Code" class="mx-auto w-48 h-48 mb-4 border rounded-lg">
            @endif

            @if($milestone->payment_link && str_contains($milestone->payment_link, 'razorpay'))
            <a href="{{ $milestone->payment_link }}" class="block w-full py-3 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700 mb-3">Pay Online Now</a>
            @else
            <div class="bg-yellow-50 text-yellow-800 px-4 py-3 rounded-lg text-sm mb-4">
                Contact {{ $milestone->plan->tenant->name }} to complete payment.<br>
                📞 {{ $milestone->plan->tenant->phone }}
            </div>
            @endif

            <p class="text-xs text-gray-400">{{ $milestone->trigger_event }}</p>
        @endif

        <div class="mt-6 pt-4 border-t text-xs text-gray-400">
            Customer: {{ $milestone->plan->customer->name }}
        </div>
    </div>
</body>
</html>
