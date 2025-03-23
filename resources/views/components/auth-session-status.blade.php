@props(['status', 'error' => null])

@if ($status)
    <div {{ $attributes->merge(['class' => 'font-medium text-sm text-green-600 p-4 bg-green-50 rounded-lg border border-green-200']) }}>
        {{ $status }}
    </div>
@endif

@if ($error)
    <div {{ $attributes->merge(['class' => 'font-medium text-sm text-red-600 p-4 bg-red-50 rounded-lg border border-red-200']) }}>
        {{ $error }}
    </div>
@elseif (session('error'))
    <div {{ $attributes->merge(['class' => 'font-medium text-sm text-red-600 p-4 bg-red-50 rounded-lg border border-red-200']) }}>
        {{ session('error') }}
    </div>
@endif
