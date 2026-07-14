<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $card->name }} — Digital Card</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-indigo-600 to-purple-800 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-sm w-full text-center">
        <div class="w-20 h-20 bg-indigo-100 rounded-full mx-auto flex items-center justify-center text-3xl mb-4">👤</div>
        <h1 class="text-2xl font-bold text-gray-900">{{ $card->name }}</h1>
        @if($card->designation)<p class="text-indigo-600 mt-1">{{ $card->designation }}</p>@endif
        <div class="mt-6 space-y-3 text-left">
            @if($card->phone)<a href="tel:{{ $card->phone }}" class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg hover:bg-indigo-50">📞 {{ $card->phone }}</a>@endif
            @if($card->email)<a href="mailto:{{ $card->email }}" class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg hover:bg-indigo-50">✉️ {{ $card->email }}</a>@endif
            @if($card->website)<a href="{{ $card->website }}" target="_blank" class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg hover:bg-indigo-50">🌐 {{ $card->website }}</a>@endif
            @if($card->phone)<a href="https://wa.me/91{{ preg_replace('/[^0-9]/', '', $card->phone) }}" target="_blank" class="flex items-center justify-center gap-2 p-3 bg-green-500 text-white rounded-lg font-medium mt-4">💬 WhatsApp</a>@endif
        </div>
        <p class="text-xs text-gray-400 mt-6">Powered by SaaS CRM</p>
    </div>
</body>
</html>
