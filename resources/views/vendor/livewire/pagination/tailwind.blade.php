<div>
    @if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex items-center justify-end">

        {{-- Mobile: simple prev/next only --}}
        <div class="flex justify-between flex-1 sm:hidden gap-2">
            @if ($paginator->onFirstPage())
                <span class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-lg cursor-not-allowed"
                      style="background:var(--bg-soft);color:var(--ink-mute);border:1px solid var(--line)">
                    {!! __('pagination.previous') !!}
                </span>
            @else
                <button type="button" wire:click="previousPage('{{ $paginator->getPageName() }}')"
                        class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-lg transition-colors"
                        style="background:#fff;color:var(--ink-soft);border:1px solid var(--line)"
                        onmouseenter="this.style.background='var(--bg-soft)'" onmouseleave="this.style.background='#fff'">
                    {!! __('pagination.previous') !!}
                </button>
            @endif

            @if ($paginator->hasMorePages())
                <button type="button" wire:click="nextPage('{{ $paginator->getPageName() }}')"
                        class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-lg transition-colors"
                        style="background:#fff;color:var(--ink-soft);border:1px solid var(--line)"
                        onmouseenter="this.style.background='var(--bg-soft)'" onmouseleave="this.style.background='#fff'">
                    {!! __('pagination.next') !!}
                </button>
            @else
                <span class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-lg cursor-not-allowed"
                      style="background:var(--bg-soft);color:var(--ink-mute);border:1px solid var(--line)">
                    {!! __('pagination.next') !!}
                </span>
            @endif
        </div>

        {{-- Desktop: full page number buttons --}}
        <div class="hidden sm:flex sm:items-center sm:gap-1">

            {{-- Previous Page --}}
            @if ($paginator->onFirstPage())
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg cursor-not-allowed"
                      style="background:var(--bg-soft);color:var(--ink-mute);border:1px solid var(--line)" aria-disabled="true">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                </span>
            @else
                <button type="button"
                        wire:click="previousPage('{{ $paginator->getPageName() }}')"
                        x-on:click="{{ $scrollIntoViewJsSnippet }}"
                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg transition-colors"
                        style="background:#fff;color:var(--ink-soft);border:1px solid var(--line)"
                        onmouseenter="this.style.background='var(--bg-soft)'" onmouseleave="this.style.background='#fff'"
                        aria-label="{{ __('pagination.previous') }}">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                </button>
            @endif

            {{-- Page Numbers --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="inline-flex items-center justify-center w-8 h-8 text-xs font-mono rounded-lg cursor-default"
                          style="color:var(--ink-mute)">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        <span wire:key="paginator-{{ $paginator->getPageName() }}-page{{ $page }}">
                            @if ($page == $paginator->currentPage())
                                <span aria-current="page"
                                      class="inline-flex items-center justify-center w-8 h-8 text-xs font-semibold font-mono rounded-lg cursor-default"
                                      style="background:var(--accent);color:#fff;border:1px solid var(--accent)">
                                    {{ $page }}
                                </span>
                            @else
                                <button type="button"
                                        wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')"
                                        x-on:click="{{ $scrollIntoViewJsSnippet }}"
                                        class="inline-flex items-center justify-center w-8 h-8 text-xs font-mono rounded-lg transition-colors"
                                        style="background:#fff;color:var(--ink-soft);border:1px solid var(--line)"
                                        onmouseenter="this.style.background='var(--bg-soft)'" onmouseleave="this.style.background='#fff'"
                                        aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                                    {{ $page }}
                                </button>
                            @endif
                        </span>
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page --}}
            @if ($paginator->hasMorePages())
                <button type="button"
                        wire:click="nextPage('{{ $paginator->getPageName() }}')"
                        x-on:click="{{ $scrollIntoViewJsSnippet }}"
                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg transition-colors"
                        style="background:#fff;color:var(--ink-soft);border:1px solid var(--line)"
                        onmouseenter="this.style.background='var(--bg-soft)'" onmouseleave="this.style.background='#fff'"
                        aria-label="{{ __('pagination.next') }}">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                    </svg>
                </button>
            @else
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg cursor-not-allowed"
                      style="background:var(--bg-soft);color:var(--ink-mute);border:1px solid var(--line)" aria-disabled="true">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                    </svg>
                </span>
            @endif

        </div>
    </nav>
    @endif
</div>
