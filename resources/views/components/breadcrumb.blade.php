@props(['items' => []])

<nav class="flex flex-wrap items-center space-x-2 text-sm text-[var(--cc-text-muted)] font-medium mb-6">
    <!-- Home button pointing to dashboard -->
    <a href="{{ route('dashboard') }}" class="text-[var(--cc-text-muted)] hover:text-[var(--cc-text)] transition-colors flex items-center" aria-label="Home">
        <svg width="20" height="20" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg" class="h-[18px] w-[18px] mb-[2px]">
            <path d="M16 7.609c.352 0 .69.122.96.343l.111.1 6.25 6.25v.001a1.5 1.5 0 0 1 .445 1.071v7.5a.89.89 0 0 1-.891.891H9.125a.89.89 0 0 1-.89-.89v-7.5l.006-.149a1.5 1.5 0 0 1 .337-.813l.1-.11 6.25-6.25c.285-.285.67-.444 1.072-.444Zm5.984 7.876L16 9.5l-5.984 5.985v6.499h11.968z" fill="currentColor" stroke="currentColor" stroke-width=".094"/>
        </svg>
    </a>

    @foreach($items as $i => $item)
        <!-- Chevron Separator -->
        <svg width="12" height="12" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" class="text-gray-300 dark:text-gray-600 flex items-center">
            <path d="m14.413 10.663-6.25 6.25a.939.939 0 1 1-1.328-1.328L12.42 10 6.836 4.413a.939.939 0 1 1 1.328-1.328l6.25 6.25a.94.94 0 0 1-.001 1.328" fill="currentColor"/>
        </svg>

        @if($i < count($items) - 1)
            <a href="{{ $item['url'] }}" class="hover:text-[var(--cc-text)] transition-colors">{{ $item['label'] }}</a>
        @else
            <span class="text-indigo-500 dark:text-indigo-400 font-semibold">{{ $item['label'] }}</span>
        @endif
    @endforeach
</nav>
