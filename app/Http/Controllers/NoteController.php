<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\Tag;
use App\Support\HtmlSanitizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class NoteController extends Controller
{
    public function __construct(private HtmlSanitizer $sanitizer)
    {
    }

    /**
     * All notes, or — with ?tag=name — just the notes carrying that tag.
     * The active tag is also what a new note gets pre-tagged with.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        $activeTag = null;
        if ($request->filled('tag')) {
            $activeTag = $user->tags()->where('name', Str::lower($request->query('tag')))->first();

            if ($activeTag === null) {
                return redirect()->route('notes.index');
            }
        }

        $notes = ($activeTag ? $activeTag->notes() : $user->notes())
            ->with('tags')
            ->latest('updated_at')
            ->latest('id')
            ->get();

        $tags = $user->tags()
            ->withCount('notes')
            ->orderBy('name')
            ->get();

        return view('notes.index', [
            'notes' => $notes,
            'tags' => $tags,
            'activeTag' => $activeTag,
            'totalCount' => $activeTag ? $user->notes()->count() : $notes->count(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        // Like Keep: closing an empty note just discards it.
        if ($data['title'] === null && $data['body'] === '') {
            return $this->backToList($request);
        }

        $note = $request->user()->notes()->create([
            'title' => $data['title'],
            'body' => $data['body'],
        ]);

        $this->syncTags($request, $note);

        return $this->backToList($request)->with('status', 'note-saved');
    }

    public function update(Request $request, Note $note): RedirectResponse
    {
        abort_unless($note->user_id === $request->user()->id, 404);

        $data = $this->validated($request);

        $note->fill([
            'title' => $data['title'],
            'body' => $data['body'],
        ]);
        // Only bump updated_at (and so the note's position) on real edits.
        $note->save();

        $this->syncTags($request, $note);

        return $this->backToList($request)->with('status', 'note-updated');
    }

    public function destroy(Request $request, Note $note): RedirectResponse
    {
        abort_unless($note->user_id === $request->user()->id, 404);

        $note->delete();
        $this->pruneUnusedTags($request);

        return $this->backToList($request)->with('status', 'note-deleted');
    }

    /**
     * @return array{title: ?string, body: string}
     */
    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'body' => ['nullable', 'string', 'max:500000'],
            'tags' => ['nullable', 'string', 'max:1000'],
            'return_tag' => ['nullable', 'string', 'max:255'],
        ]);

        $title = trim((string) ($validated['title'] ?? ''));
        $body = $this->sanitizer->clean($validated['body'] ?? '');

        // An editor that's been typed in and cleared leaves "<div><br></div>"
        // behind; treat that as no body at all.
        if (trim(html_entity_decode(strip_tags($body), ENT_QUOTES | ENT_HTML5), " \t\n\r\0\x0B\u{A0}") === '') {
            $body = '';
        }

        return [
            'title' => $title === '' ? null : $title,
            'body' => $body,
        ];
    }

    /**
     * Splits the comma-separated "tags" input into individual tag names,
     * finds-or-creates each one scoped to the current user, and syncs
     * them onto the note. Tags left with no notes are removed so the
     * sidebar doesn't fill up with dead entries.
     */
    private function syncTags(Request $request, Note $note): void
    {
        $names = collect(explode(',', (string) $request->input('tags', '')))
            ->map(fn ($name) => Str::lower(trim($name)))
            ->filter()
            ->unique();

        $tagIds = $names->map(
            fn ($name) => Tag::firstOrCreate(
                ['user_id' => $request->user()->id, 'name' => Str::limit($name, 255, '')]
            )->id
        );

        $changes = $note->tags()->sync($tagIds);

        // A tag-only edit should still float the note to the top.
        if (! empty($changes['attached']) || ! empty($changes['detached'])) {
            $note->touch();
        }

        $this->pruneUnusedTags($request);
    }

    private function pruneUnusedTags(Request $request): void
    {
        $request->user()->tags()->whereDoesntHave('notes')->delete();
    }

    /**
     * Back to whichever list the user was looking at, as long as that
     * tag still exists.
     */
    private function backToList(Request $request): RedirectResponse
    {
        $tag = Str::lower(trim((string) $request->input('return_tag', '')));

        if ($tag !== '' && $request->user()->tags()->where('name', $tag)->exists()) {
            return redirect()->route('notes.index', ['tag' => $tag]);
        }

        return redirect()->route('notes.index');
    }
}
