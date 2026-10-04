<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Note extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'body',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)->orderBy('name');
    }

    /**
     * The note's text with all formatting stripped, for client-side
     * search and for deciding whether a note is effectively empty.
     */
    public function plainText(): string
    {
        $text = preg_replace('/<(br|\/div|\/p|\/li|\/h1|\/blockquote|\/pre)[^>]*>/i', ' ', (string) $this->body);

        return trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5)));
    }
}
