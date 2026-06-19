{{-- Skin switch: MODERN (desain asli) ↔ CLASSIC (Claude Design). Server-side via cookie. --}}
@php($skin = $skin ?? 'modern')
<div class="hidden sm:flex items-center rounded-xl overflow-hidden select-none"
     style="background:var(--cc-card);border:1px solid var(--cc-border);"
     title="Switch design — Modern / Classic">
    <a href="{{ route('skin.switch', 'modern') }}"
       class="px-2.5 py-1.5 text-[10px] font-black transition-colors"
       style="{{ $skin === 'modern' ? 'color:#fff;background:var(--cc-accent)' : 'color:var(--cc-text-muted);background:transparent' }}">MODERN</a>
    <a href="{{ route('skin.switch', 'classic') }}"
       class="px-2.5 py-1.5 text-[10px] font-black transition-colors"
       style="{{ $skin === 'classic' ? 'color:#fff;background:var(--cc-accent)' : 'color:var(--cc-text-muted);background:transparent' }}">CLASSIC</a>
</div>
