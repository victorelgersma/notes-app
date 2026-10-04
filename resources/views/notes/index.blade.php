@extends('layouts.app')

@if ($activeTag)
    @section('title', $activeTag->name)
@endif

@php
    $tagNames = $tags->pluck('name')->values();
    $initialTags = $activeTag ? [$activeTag->name] : [];
    $navItem = 'flex items-center gap-3 pl-6 pr-4 py-2.5 rounded-r-full text-sm transition-colors';
    $navIdle = 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-900';
    $navActive = 'bg-gray-900 text-white dark:bg-gray-100 dark:text-gray-900 font-medium';
@endphp

{{-- ================= Tags sidebar ================= --}}
@section('sidebar')
    {{-- Backdrop for the mobile drawer --}}
    <div
        x-show="sidebarOpen"
        x-cloak
        x-transition.opacity
        @click="sidebarOpen = false"
        class="lg:hidden fixed inset-0 top-16 z-20 bg-black/30"
    ></div>

    <aside
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        class="fixed top-16 bottom-0 left-0 z-20 w-64 bg-white dark:bg-gray-950 border-r border-gray-200 dark:border-gray-800 transition-transform
               lg:translate-x-0 lg:sticky lg:top-16 lg:h-[calc(100vh-4rem)] lg:border-r-0 shrink-0 overflow-y-auto py-3 pr-3"
    >
        <a href="{{ route('notes.index') }}" class="{{ $navItem }} {{ $activeTag ? $navIdle : $navActive }}">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5 shrink-0 opacity-70">
                <path d="M10 1a6 6 0 0 0-3.815 10.631C7.237 12.5 8 13.443 8 14.456v.644a.75.75 0 0 0 .572.729 6.016 6.016 0 0 0 2.856 0A.75.75 0 0 0 12 15.1v-.644c0-1.013.762-1.957 1.815-2.825A6 6 0 0 0 10 1ZM8.863 17.414a.75.75 0 0 0-.226 1.483 9.066 9.066 0 0 0 2.726 0 .75.75 0 0 0-.226-1.483 7.553 7.553 0 0 1-2.274 0Z"/>
            </svg>
            <span class="flex-1">Notes</span>
            <span class="text-xs tabular-nums opacity-60">{{ $totalCount }}</span>
        </a>

        <p class="mt-5 mb-1 pl-6 text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-600">Tags</p>

        @forelse ($tags as $tag)
            <div x-data="{ renaming: false }" class="group relative">
                <a
                    x-show="!renaming"
                    href="{{ route('notes.index', ['tag' => $tag->name]) }}"
                    class="{{ $navItem }} {{ $activeTag?->is($tag) ? $navActive : $navIdle }}"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5 shrink-0 opacity-70">
                        <path fill-rule="evenodd" d="M4.5 2A2.5 2.5 0 0 0 2 4.5v3.879a2.5 2.5 0 0 0 .732 1.767l7.5 7.5a2.5 2.5 0 0 0 3.536 0l3.878-3.878a2.5 2.5 0 0 0 0-3.536l-7.5-7.5A2.5 2.5 0 0 0 8.38 2H4.5ZM5 6a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd"/>
                    </svg>
                    <span class="flex-1 truncate">{{ $tag->name }}</span>
                    {{-- The count makes way for the rename button on hover. --}}
                    <span class="w-6 text-right text-xs tabular-nums opacity-60 group-hover:invisible group-focus-within:invisible">{{ $tag->notes_count }}</span>
                </a>

                <button
                    x-show="!renaming"
                    type="button"
                    title="Rename tag"
                    aria-label="Rename tag {{ $tag->name }}"
                    @click="renaming = true; $nextTick(() => { $refs.name.focus(); $refs.name.select(); })"
                    class="absolute right-3 top-1/2 -translate-y-1/2 p-1 rounded-md opacity-0 group-hover:opacity-100 focus:opacity-100
                           {{ $activeTag?->is($tag) ? 'text-white dark:text-gray-900 hover:bg-white/15 dark:hover:bg-black/10' : 'text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-800' }}"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
                        <path d="m2.695 14.762-1.262 3.155a.5.5 0 0 0 .65.65l3.155-1.262a4 4 0 0 0 1.343-.886L17.5 5.501a2.121 2.121 0 0 0-3-3L3.58 13.419a4 4 0 0 0-.885 1.343Z"/>
                    </svg>
                </button>

                {{-- Enter or clicking away saves; Escape backs out. --}}
                <form
                    x-show="renaming"
                    x-cloak
                    x-ref="renameForm"
                    method="POST"
                    action="{{ route('tags.update', $tag) }}"
                    class="flex items-center gap-3 pl-6 pr-4 py-1.5"
                >
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="return_tag" value="{{ $activeTag?->name }}">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5 shrink-0 opacity-70">
                        <path fill-rule="evenodd" d="M4.5 2A2.5 2.5 0 0 0 2 4.5v3.879a2.5 2.5 0 0 0 .732 1.767l7.5 7.5a2.5 2.5 0 0 0 3.536 0l3.878-3.878a2.5 2.5 0 0 0 0-3.536l-7.5-7.5A2.5 2.5 0 0 0 8.38 2H4.5ZM5 6a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd"/>
                    </svg>
                    <input
                        type="text"
                        name="name"
                        x-ref="name"
                        value="{{ $tag->name }}"
                        maxlength="255"
                        autocomplete="off"
                        aria-label="Tag name"
                        @keydown.enter.prevent="$el.blur()"
                        @keydown.escape.stop="$el.value = $el.defaultValue; renaming = false"
                        @blur="
                            const name = $el.value.trim().toLowerCase();
                            if (renaming && name !== '' && name !== $el.defaultValue) $refs.renameForm.submit();
                            else { $el.value = $el.defaultValue; renaming = false; }
                        "
                        class="flex-1 min-w-0 bg-transparent border-0 border-b border-gray-400 dark:border-gray-600 px-0 py-1 text-sm focus:ring-0 focus:border-gray-900 dark:focus:border-gray-100 outline-none"
                    >
                </form>
            </div>
        @empty
            <p class="pl-6 pr-2 py-2 text-sm text-gray-400 dark:text-gray-600">
                Tags you add to notes show up here.
            </p>
        @endforelse
    </aside>
@endsection

{{-- ================= Notes ================= --}}
@section('content')
<div
    class="px-4 sm:px-8 py-8"
    x-data="notesPage({
        updateUrl: @js(route('notes.update', '__ID__')),
        notes: @js($notes->map(fn ($n) => [
            'id' => $n->id,
            'text' => Str::lower(($n->title ?? '').' '.$n->plainText().' '.$n->tags->pluck('name')->implode(' ')),
        ])),
    })"
>
    {{-- ---------- Composer: "Take a note…" ---------- --}}
    <div class="max-w-xl mx-auto mb-10" @click.outside="if (composerOpen && !editing) closeComposer()">
        <div
            x-show="!composerOpen"
            @click="openComposer()"
            @keydown.enter.prevent="openComposer()"
            tabindex="0"
            role="button"
            class="flex items-center gap-3 px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-md cursor-text text-gray-500 dark:text-gray-400 transition-shadow focus:outline-none focus:ring-2 focus:ring-gray-900 dark:focus:ring-gray-100"
        >
            <span class="flex-1">Take a note…</span>
            @if ($activeTag)
                <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300">{{ $activeTag->name }}</span>
            @endif
        </div>

        <form
            x-show="composerOpen"
            x-cloak
            x-ref="composerForm"
            method="POST"
            action="{{ route('notes.store') }}"
            class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-lg"
        >
            @csrf
            <input type="hidden" name="return_tag" value="{{ $activeTag?->name }}">

            <div class="px-4 pt-3">
                <input
                    type="text"
                    name="title"
                    x-ref="composerTitle"
                    placeholder="Title"
                    maxlength="255"
                    autocomplete="off"
                    @keydown.enter.prevent="$refs.composerEditor.focus()"
                    class="w-full bg-transparent border-0 p-0 text-base font-semibold focus:ring-0 outline-none placeholder:text-gray-400 dark:placeholder:text-gray-600"
                >
            </div>

            <div class="px-4 pt-2">
                <input type="hidden" name="body" id="composer-body">
                <trix-editor
                    x-ref="composerEditor"
                    input="composer-body"
                    toolbar="composer-toolbar"
                    placeholder="Take a note…"
                    class="note-content text-sm"
                ></trix-editor>
            </div>

            <div class="px-4 pt-3">
                @include('notes._tag-input', ['initial' => $initialTags, 'available' => $tagNames, 'reset' => 'composer-reset'])
            </div>

            <div class="flex items-center gap-2 px-2 py-2 mt-2 border-t border-gray-100 dark:border-gray-800">
                <trix-toolbar id="composer-toolbar" class="flex-1 min-w-0"></trix-toolbar>
                <button
                    type="button"
                    @click="closeComposer()"
                    class="shrink-0 text-sm font-medium px-4 py-1.5 rounded-md hover:bg-gray-100 dark:hover:bg-gray-800"
                >
                    Close
                </button>
            </div>
        </form>

        @if ($errors->any())
            <p class="mt-2 text-sm text-red-600">{{ $errors->first() }}</p>
        @endif
    </div>

    {{-- ---------- Heading for a tag view ---------- --}}
    @if ($activeTag)
        <div class="max-w-6xl mx-auto mb-4 flex items-baseline gap-3">
            <h1 class="text-xl font-semibold">{{ $activeTag->name }}</h1>
            <span class="text-sm text-gray-400 dark:text-gray-600">
                {{ $notes->count() }} {{ Str::plural('note', $notes->count()) }}
            </span>
        </div>
    @endif

    {{-- ---------- Notes grid (masonry via CSS columns) ---------- --}}
    @if ($notes->isEmpty())
        <div class="flex flex-col items-center text-center text-gray-400 dark:text-gray-600 py-24">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" class="w-20 h-20 mb-4 opacity-60">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 0 0 1.5-.189m-1.5.189a6.01 6.01 0 0 1-1.5-.189m3.75 7.478a12.06 12.06 0 0 1-4.5 0m3.75 2.383a14.406 14.406 0 0 1-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 1 0-7.517 0c.85.493 1.509 1.333 1.509 2.316V18"/>
            </svg>
            <p>Notes you add appear here</p>
        </div>
    @else
        <div x-show="visibleCount(search) === 0" x-cloak class="text-center text-gray-400 dark:text-gray-600 py-24">
            No notes match “<span x-text="search"></span>”.
        </div>

        <div class="max-w-6xl mx-auto columns-1 sm:columns-2 lg:columns-3 2xl:columns-4 gap-4">
            @foreach ($notes as $note)
                @include('notes._card', ['note' => $note])
            @endforeach
        </div>
    @endif

    {{-- ---------- Edit modal ---------- --}}
    <div
        x-show="editing"
        x-cloak
        x-transition.opacity.duration.150ms
        @click.self="closeNote()"
        class="fixed inset-0 z-40 bg-black/40 flex items-start justify-center p-4 pt-[8vh]"
        role="dialog"
        aria-modal="true"
    >
        <form
            x-ref="editForm"
            method="POST"
            :action="editing ? updateUrl.replace('__ID__', editing.id) : ''"
            class="w-full max-w-2xl max-h-[84vh] flex flex-col rounded-xl bg-white dark:bg-gray-900 shadow-2xl border border-gray-200 dark:border-gray-800"
        >
            @csrf
            @method('PUT')
            <input type="hidden" name="return_tag" value="{{ $activeTag?->name }}">

            <div class="px-5 pt-4">
                <input
                    type="text"
                    name="title"
                    x-ref="editTitle"
                    placeholder="Title"
                    maxlength="255"
                    autocomplete="off"
                    @keydown.enter.prevent="$refs.editEditor.focus()"
                    class="w-full bg-transparent border-0 p-0 text-lg font-semibold focus:ring-0 outline-none placeholder:text-gray-400 dark:placeholder:text-gray-600"
                >
            </div>

            <div class="px-5 pt-2 flex-1 overflow-y-auto">
                <input type="hidden" name="body" id="edit-body">
                <trix-editor
                    x-ref="editEditor"
                    input="edit-body"
                    toolbar="edit-toolbar"
                    placeholder="Note"
                    class="note-content min-h-[8rem]"
                ></trix-editor>
            </div>

            <div class="px-5 pt-3">
                @include('notes._tag-input', ['initial' => [], 'available' => $tagNames, 'reset' => 'note-opened'])
            </div>

            <p class="px-5 pt-2 text-right text-xs text-gray-400 dark:text-gray-600" x-text="editing ? 'Edited ' + editing.edited : ''"></p>

            <div class="flex items-center gap-1 px-2 py-2 mt-1 border-t border-gray-100 dark:border-gray-800">
                <trix-toolbar id="edit-toolbar" class="flex-1 min-w-0"></trix-toolbar>
                <button
                    type="submit"
                    form="delete-note-form"
                    title="Delete note"
                    class="shrink-0 p-2 rounded-md text-gray-400 hover:text-red-600 hover:bg-gray-100 dark:hover:bg-gray-800"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
                        <path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 0 0 6 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 1 0 .23 1.482l.149-.022.841 10.518A2.75 2.75 0 0 0 7.596 19h4.807a2.75 2.75 0 0 0 2.742-2.53l.841-10.52.149.023a.75.75 0 0 0 .23-1.482A41.03 41.03 0 0 0 14 4.193V3.75A2.75 2.75 0 0 0 11.25 1h-2.5ZM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4ZM8.58 7.72a.75.75 0 0 0-1.5.06l.3 7.5a.75.75 0 1 0 1.5-.06l-.3-7.5Zm4.34.06a.75.75 0 1 0-1.5-.06l-.3 7.5a.75.75 0 1 0 1.5.06l.3-7.5Z" clip-rule="evenodd"/>
                    </svg>
                </button>
                <button
                    type="button"
                    @click="closeNote()"
                    class="shrink-0 text-sm font-medium px-4 py-1.5 rounded-md hover:bg-gray-100 dark:hover:bg-gray-800"
                >
                    Close
                </button>
            </div>
        </form>

        {{-- Lives outside the edit form (forms can't nest); the trash
             button above submits it via form="delete-note-form". --}}
        <form
            id="delete-note-form"
            method="POST"
            :action="editing ? updateUrl.replace('__ID__', editing.id) : ''"
            onsubmit="return confirm('Delete this note?')"
            class="hidden"
        >
            @csrf
            @method('DELETE')
            <input type="hidden" name="return_tag" value="{{ $activeTag?->name }}">
        </form>
    </div>

    {{-- ---------- Toast ---------- --}}
    @if (session('status'))
        <div
            x-data="{ show: true }"
            x-init="setTimeout(() => show = false, 2500)"
            x-show="show"
            x-transition.opacity.duration.300ms
            class="fixed bottom-5 left-5 z-50 px-4 py-2.5 rounded-lg bg-gray-900 text-white dark:bg-gray-100 dark:text-gray-900 text-sm shadow-lg"
        >
            {{ ['note-saved' => 'Note saved', 'note-updated' => 'Note updated', 'note-deleted' => 'Note deleted', 'tag-renamed' => 'Tag renamed'][session('status')] ?? 'Done' }}
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    // Page-level Alpine component: the composer, the edit modal and the
    // search count. Defined as a plain script so it exists on window
    // before Alpine's deferred script boots.
    const afterRender = () => new Promise(resolve =>
        requestAnimationFrame(() => requestAnimationFrame(resolve)));

    window.notesPage = function ({ updateUrl, notes }) {
        return {
            updateUrl,
            notes,
            composerOpen: false,
            editing: null,
            snapshot: '',

            init() {
                // Keep-style shortcuts: "c" for a new note, "/" to search.
                window.addEventListener('keydown', (e) => {
                    if (e.metaKey || e.ctrlKey || e.altKey) return;
                    if (e.target.closest('input, textarea, select, trix-editor, [contenteditable]')) return;
                    if (this.editing) return;

                    if (e.key === 'c') {
                        e.preventDefault();
                        this.openComposer();
                    } else if (e.key === '/') {
                        e.preventDefault();
                        document.querySelector('input[type=search]')?.focus();
                    }
                });

                window.addEventListener('keydown', (e) => {
                    if (e.key !== 'Escape') return;
                    if (this.editing) this.closeNote();
                    else if (this.composerOpen) this.closeComposer();
                });
            },

            // `search` lives on the layout's Alpine scope, so it's passed in.
            visibleCount(search) {
                const q = (search ?? '').trim().toLowerCase();
                return this.notes.filter(n => q === '' || n.text.includes(q)).length;
            },

            matches(text, search) {
                const q = (search ?? '').trim().toLowerCase();
                return q === '' || text.includes(q);
            },

            // ---- composer ----
            openComposer() {
                this.composerOpen = true;
                this.$nextTick(() => this.$refs.composerEditor.focus());
            },

            async closeComposer() {
                window.dispatchEvent(new CustomEvent('flush-tags'));
                await this.$nextTick();

                const form = this.$refs.composerForm;
                const hasTitle = this.$refs.composerTitle.value.trim() !== '';
                const hasBody = this.$refs.composerEditor.editor.getDocument().toString().trim() !== '';

                if (hasTitle || hasBody) {
                    form.submit();
                    return;
                }

                // Nothing typed — just fold it back up.
                this.$refs.composerTitle.value = '';
                this.$refs.composerEditor.editor.loadHTML('');
                window.dispatchEvent(new CustomEvent('composer-reset'));
                this.composerOpen = false;
            },

            // ---- edit modal ----
            openNote(note) {
                if (this.composerOpen) return;

                this.editing = note;
                this.$refs.editTitle.value = note.title ?? '';
                this.$refs.editEditor.editor.loadHTML(note.body ?? '');
                window.dispatchEvent(new CustomEvent('note-opened', { detail: { tags: note.tags } }));

                // Trix renders (and fills its hidden input) on the next
                // animation frame, so wait for that before taking the
                // "unchanged" snapshot.
                afterRender().then(() => {
                    this.snapshot = this.serializeEdit();
                    const editor = this.$refs.editEditor.editor;
                    this.$refs.editEditor.focus();
                    editor.setSelectedRange(editor.getDocument().getLength() - 1);
                });
            },

            serializeEdit() {
                const fields = this.$refs.editForm.elements;
                return JSON.stringify([
                    fields.namedItem('title').value,
                    fields.namedItem('body').value,
                    fields.namedItem('tags').value,
                ]);
            },

            async closeNote() {
                if (!this.editing) return;

                window.dispatchEvent(new CustomEvent('flush-tags'));
                await this.$nextTick();
                await afterRender();

                // Closing saves, like Keep — but only if something changed.
                if (this.serializeEdit() !== this.snapshot) {
                    this.$refs.editForm.submit();
                    return;
                }

                this.editing = null;
            },
        };
    };
</script>
@endpush
