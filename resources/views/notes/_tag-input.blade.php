{{--
    Tag chip input: typing a comma or pressing Enter turns the current
    text into a chip (or picks the highlighted autocomplete suggestion);
    backspace on an empty field removes the last chip. The hidden input
    carries the comma-joined list under the "tags" name the controller
    parses.

    $initial — tag names to start with
    $reset   — window event that resets the chips; its detail.tags (if
               given) become the new chips, otherwise $initial is restored
--}}
<div
    x-data="tagInput(@js($initial), @js($available))"
    x-on:{{ $reset }}.window="setTags($event.detail?.tags)"
    x-on:flush-tags.window="addTag()"
    class="flex flex-wrap items-center gap-2 min-h-8"
>
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4 text-gray-300 dark:text-gray-600 shrink-0" aria-hidden="true">
        <path fill-rule="evenodd" d="M4.5 2A2.5 2.5 0 0 0 2 4.5v3.879a2.5 2.5 0 0 0 .732 1.767l7.5 7.5a2.5 2.5 0 0 0 3.536 0l3.878-3.878a2.5 2.5 0 0 0 0-3.536l-7.5-7.5A2.5 2.5 0 0 0 8.38 2H4.5ZM5 6a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd"/>
    </svg>
    <template x-for="(tag, index) in tags" :key="tag">
        <span class="inline-flex items-center gap-1 text-xs pl-2.5 pr-1.5 py-1 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200">
            <span x-text="tag"></span>
            <button type="button" @click="removeTag(index)" class="text-gray-400 hover:text-gray-700 dark:hover:text-gray-100" aria-label="Remove tag">&times;</button>
        </span>
    </template>
    <div class="relative flex-1 min-w-[6rem]">
        <input
            type="text"
            x-model="draft"
            @keydown.enter.prevent="selectHighlighted()"
            @keydown.comma.prevent="selectHighlighted()"
            @keydown.down.prevent="moveHighlight(1)"
            @keydown.up.prevent="moveHighlight(-1)"
            @keydown.escape="if (draft !== '') { $event.stopPropagation(); draft = ''; highlightedIndex = -1 }"
            @keydown.backspace="removeLastTag()"
            @blur="addTag()"
            placeholder="Add tag…"
            aria-label="Tags"
            class="w-full bg-transparent border-0 p-0 text-sm focus:ring-0 outline-none dark:text-gray-100 placeholder:text-gray-400 dark:placeholder:text-gray-600"
            autocomplete="off"
        >
        <ul
            x-show="suggestions.length > 0"
            x-cloak
            class="absolute z-50 bottom-full mb-2 w-52 max-h-48 overflow-auto rounded-xl py-1 border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-lg text-sm"
        >
            <template x-for="(name, i) in suggestions" :key="name">
                <li
                    @mousedown.prevent="addTag(name)"
                    :class="i === highlightedIndex ? 'bg-gray-100 dark:bg-gray-700' : ''"
                    class="px-3.5 py-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200"
                    x-text="name"
                ></li>
            </template>
        </ul>
    </div>
    <input type="hidden" name="tags" :value="tags.join(',')">
</div>
