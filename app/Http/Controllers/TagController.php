<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TagController extends Controller
{
    /**
     * Renames a tag. Renaming onto a name the user already has merges the
     * two: the notes move across to the existing tag and this one goes.
     */
    public function update(Request $request, Tag $tag): RedirectResponse
    {
        abort_unless($tag->user_id === $request->user()->id, 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'return_tag' => ['nullable', 'string', 'max:255'],
        ]);

        $oldName = $tag->name;
        $newName = Str::lower(trim($validated['name']));

        if ($newName !== $oldName) {
            DB::transaction(function () use ($request, $tag, $newName) {
                $existing = $request->user()->tags()->where('name', $newName)->first();

                if ($existing === null) {
                    $tag->update(['name' => $newName]);

                    return;
                }

                $existing->notes()->syncWithoutDetaching($tag->notes()->pluck('notes.id'));
                $tag->delete();
            });
        }

        // If they were looking at the tag they just renamed, follow it.
        $returnTag = Str::lower(trim((string) ($validated['return_tag'] ?? '')));
        if ($returnTag === $oldName) {
            $returnTag = $newName;
        }

        if ($returnTag !== '' && $request->user()->tags()->where('name', $returnTag)->exists()) {
            return redirect()->route('notes.index', ['tag' => $returnTag])->with('status', 'tag-renamed');
        }

        return redirect()->route('notes.index')->with('status', 'tag-renamed');
    }
}
