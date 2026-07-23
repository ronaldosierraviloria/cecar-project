@props(['type' => 'success', 'message' => null])

@php
    if (!$message) {
        $message = session($type);
    }
@endphp

@if($message)
<div x-data="{ show: true }"
    x-cloak
    x-init="setTimeout(() => show = false, 4000)"
    x-show="show"
    x-transition:enter="transition ease-out duration-500"
    x-transition:enter-start="opacity-0 translate-y-8 scale-90"
    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
    x-transition:leave="transition ease-in duration-300"
    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
    x-transition:leave-end="opacity-0 translate-y-8 scale-90"
    class="fixed bottom-10 right-10 z-50">
    <div class="{{ $type === 'success' ? 'bg-gray-900 border-gray-800' : 'bg-red-600 border-red-700' }} text-white px-6 py-4 rounded-2xl shadow-2xl flex items-center gap-3 border">
        <div class="w-8 h-8 {{ $type === 'success' ? 'bg-[#c2d500] text-[#07321e]' : 'bg-white/20' }} rounded-full flex items-center justify-center shrink-0">
            @if($type === 'success')
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            @else
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            @endif
        </div>
        <span class="text-sm font-bold">{{ $message }}</span>
    </div>
</div>
@endif
