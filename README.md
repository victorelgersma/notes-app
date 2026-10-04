# Notes

A small Google Keep–style notes app: rich-text notes, tags in a sidebar, magic-link login.
Built on the same stack as Bookmarks (Laravel 13 + SQLite + Tailwind + Alpine, served by Caddy).

## How it works

- **Take a note…** at the top expands into a composer: title, a rich-text body (Trix editor:
  bold, italic, strikethrough, links, heading, quote, code, bullet/numbered lists) and tags.
  **Done** saves it. Empty notes are discarded.
- **Tags sidebar**: click a tag to see only its notes. While a tag is selected, a new note gets
  that tag automatically, and you can still remove it or add others before closing.
- **Click a note** to open it in an editor. Closing it (Done, Esc, or clicking outside) saves
  your changes, and nothing is sent if you didn't change anything. The bin icon deletes it.
- Tags that no longer have any notes are removed, so the sidebar stays tidy.
- Search in the top bar filters as you type (title, body text and tags).
- Shortcuts: `c` new note, `/` search, `Esc` close.

Note bodies are HTML. Every save goes through an allowlist sanitizer (`app/Support/HtmlSanitizer.php`),
so pasted scripts, styles, images and event handlers are stripped. Links get `target="_blank"` and `rel="noopener"`.

## Setup

```sh
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
npm install
npm run build
```

Configure `MAIL_*` in `.env` so login links can be sent (locally, `MAIL_MAILER=log` writes them to
`storage/logs/laravel.log`).

## Tests

```sh
php artisan test
```
# notes-app

