@php
    $payload = [
        'id' => $note->id,
        'title' => $note->title,
        'body' => $note->body,
        'tags' => $note->tags->pluck('name')->values(),
        'edited' => $note->updated_at->format('j M Y'),
    ];
    $searchText = Str::lower(($note->title ?? '').' '.$note->plainText().' '.$note->tags->pluck('name')->implode(' '));
@endphp

<article
    x-data="{ note: @js($payload) }"
    data-note-card
    data-search="{{ $searchText }}"
    x-show="matches($el.dataset.search, search)"
    tabindex="0"
    @click="if (!$event.target.closest('a')) openNote(note)"
    @keydown.enter.self="openNote(note)"
    class="break-inside-avoid mb-4 rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 px-4 py-3 cursor-default transition-shadow hover:shadow-md focus:outline-none focus:ring-2 focus:ring-gray-900 dark:focus:ring-gray-100"
>
    @if ($note->title)
        <h2 class="font-semibold leading-snug break-words mb-1">{{ $note->title }}</h2>
    @endif

    @if ($note->body)
        <div
            x-data="{ clipped: false }"
            x-init="$nextTick(() => clipped = $el.scrollHeight > $el.clientHeight + 1)"
            :class="clipped && 'note-preview--clipped'"
            class="note-content note-preview text-sm text-gray-700 dark:text-gray-300"
        >{!! $note->body !!}</div>
    @endif

    @if ($note->tags->isNotEmpty())
        <div class="mt-3 flex flex-wrap gap-1.5">
            @foreach ($note->tags as $tag)
                <a
                    href="{{ route('notes.index', ['tag' => $tag->name]) }}"
                    class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors"
                >{{ $tag->name }}</a>
            @endforeach
        </div>
    @endif
</article>
