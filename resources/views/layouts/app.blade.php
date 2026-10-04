<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@hasSection('title')@yield('title') · @endif{{ config('app.name', 'Notes') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdnjs.cloudflare.com/ajax/libs/alpinejs/3.14.1/cdn.min.js"></script>
</head>
<body class="h-full bg-white text-gray-900 dark:bg-gray-950 dark:text-gray-100 antialiased">
    <div class="min-h-full flex flex-col" x-data="{ search: '', sidebarOpen: false }">
        <nav class="sticky top-0 z-30 h-16 flex items-center gap-3 px-4 sm:px-6 border-b border-gray-200 dark:border-gray-800 bg-white/90 dark:bg-gray-950/90 backdrop-blur">
            @hasSection('sidebar')
                <button
                    type="button"
                    @click="sidebarOpen = !sidebarOpen"
                    class="lg:hidden -ml-1 p-2 rounded-full text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800"
                    aria-label="Toggle tags"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
                        <path fill-rule="evenodd" d="M2 4.75A.75.75 0 0 1 2.75 4h14.5a.75.75 0 0 1 0 1.5H2.75A.75.75 0 0 1 2 4.75ZM2 10a.75.75 0 0 1 .75-.75h14.5a.75.75 0 0 1 0 1.5H2.75A.75.75 0 0 1 2 10Zm0 5.25a.75.75 0 0 1 .75-.75h14.5a.75.75 0 0 1 0 1.5H2.75a.75.75 0 0 1-.75-.75Z" clip-rule="evenodd"/>
                    </svg>
                </button>
            @endif

            <a href="{{ auth()->check() ? route('notes.index') : route('login') }}" class="font-semibold tracking-tight text-lg shrink-0 lg:w-52">Notes</a>

            @hasSection('sidebar')
                <div class="flex-1 max-w-2xl">
                    <label class="relative block">
                        <span class="sr-only">Search notes</span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400">
                            <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 1 0 0 11 5.5 5.5 0 0 0 0-11ZM2 9a7 7 0 1 1 12.452 4.391l3.328 3.329a.75.75 0 1 1-1.06 1.06l-3.329-3.328A7 7 0 0 1 2 9Z" clip-rule="evenodd"/>
                        </svg>
                        <input
                            type="search"
                            x-model="search"
                            x-ref="search"
                            @keydown.escape="search = ''; $el.blur()"
                            placeholder="Search"
                            class="w-full pl-10 pr-3 py-2 rounded-lg bg-gray-100 dark:bg-gray-900 border-0 text-sm focus:bg-white dark:focus:bg-gray-900 focus:ring-2 focus:ring-gray-900 dark:focus:ring-gray-100 outline-none placeholder:text-gray-500"
                        >
                    </label>
                </div>
            @else
                <div class="flex-1"></div>
            @endif

            @auth
                <form method="POST" action="{{ route('logout') }}" class="shrink-0">
                    @csrf
                    <button type="submit" class="text-sm text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100">
                        Log out
                    </button>
                </form>
            @endauth
        </nav>

        <div class="flex-1 flex">
            @yield('sidebar')

            <main class="flex-1 min-w-0">
                @yield('content')
            </main>
        </div>

        <footer class="px-6 py-4 text-center text-xs text-gray-400 dark:text-gray-600 border-t border-gray-200 dark:border-gray-800">
            <a
                href="https://github.com/victorelgersma/notes.vjbe.net"
                target="_blank"
                rel="noopener noreferrer"
                class="inline-flex items-center gap-1.5 hover:text-gray-700 dark:hover:text-gray-300 transition-colors"
            >
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="w-4 h-4">
                    <path d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.01 8.01 0 0 0 16 8c0-4.42-3.58-8-8-8Z"/>
                </svg>
                <span>View source on GitHub</span>
            </a>
        </footer>
    </div>

    <script>
        // Alpine component powering the tag-chip input: typing a comma
        // (or pressing Enter) immediately turns the current draft text
        // into a chip. This runs as a plain (non-deferred) script, so it
        // registers on window before Alpine's deferred script boots and
        // starts evaluating x-data="tagInput(...)" attributes.
        //
        // Second argument is the list of the user's existing tag names;
        // "suggestions" filters that list against the current draft text
        // for the autocomplete dropdown, and arrow keys move a highlight
        // through it.
        window.tagInput = function (initialTags, existingTagNames) {
            return {
                initial: Array.isArray(initialTags) ? initialTags.slice() : [],
                tags: Array.isArray(initialTags) ? initialTags.slice() : [],
                availableTags: Array.isArray(existingTagNames) ? existingTagNames.slice() : [],
                draft: '',
                highlightedIndex: -1,
                get suggestions() {
                    const q = this.draft.trim().toLowerCase();
                    if (q === '') return [];
                    const already = this.tags.map(t => t.toLowerCase());
                    return this.availableTags
                        .filter(name => name.toLowerCase().includes(q))
                        .filter(name => !already.includes(name.toLowerCase()))
                        .slice(0, 6);
                },
                setTags(names) {
                    this.tags = Array.isArray(names) ? names.slice() : this.initial.slice();
                    this.draft = '';
                    this.highlightedIndex = -1;
                },
                addTag(name) {
                    const value = (name ?? this.draft).trim().replace(/,+$/, '').trim();
                    this.draft = '';
                    this.highlightedIndex = -1;
                    if (value === '') return;
                    const lower = value.toLowerCase();
                    if (!this.tags.some(t => t.toLowerCase() === lower)) {
                        this.tags.push(value);
                    }
                },
                selectHighlighted() {
                    const options = this.suggestions;
                    if (this.highlightedIndex >= 0 && options[this.highlightedIndex]) {
                        this.addTag(options[this.highlightedIndex]);
                    } else {
                        this.addTag();
                    }
                },
                moveHighlight(delta) {
                    const count = this.suggestions.length;
                    if (count === 0) {
                        this.highlightedIndex = -1;
                        return;
                    }
                    this.highlightedIndex = (this.highlightedIndex + delta + count) % count;
                },
                removeTag(index) {
                    this.tags.splice(index, 1);
                },
                removeLastTag() {
                    if (this.draft === '' && this.tags.length > 0) {
                        this.tags.pop();
                    }
                },
            };
        };
    </script>
    @stack('scripts')
</body>
</html>
