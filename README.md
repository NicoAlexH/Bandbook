# Bandbook

Bandbook is a mobile-first music book for a band, built with Laravel and Vue in
one repository. It focuses on readable lyrics and chords, ordered arrangements,
and musician-specific performance views on 360–430px phone screens.

## Current features

- Public song library with fictional demo songs and song details.
- Dark performance screen with Singer, Guitarist, Bassist, Pianist, and
  Percussionist views.
- Manual section navigation, local Follow / Free state, and one-tap Rejoin.
- Text and ChordPro imports with an editable review step before saving.
- Private imported drafts stored under the signed-in user's account.
- Separate musical content and ordered section occurrences, so repeated sections
  can reference the same content.

Follow currently simulates a leader within the browser. There is no synchronization
between devices yet. Band sharing, setlists, rehearsal attendance, a full arrangement
editor, and approved performance versions are future work.

## Stack

- Laravel 13, PHP 8.5, and Laravel Fortify.
- Vue 3 Composition API, TypeScript, and Inertia.js 3.
- Tailwind CSS 4, shadcn-vue components, and Laravel Wayfinder.
- Vite Plus for frontend development and checks.
- Pest on PHPUnit, Node's test runner, and PHPStan/Larastan.
- SQLite for the current prototype. PostgreSQL is the intended database, but has
  not yet been configured or validated for this project.

## Local setup

Install PHP 8.5 with the extensions required by Composer (including SQLite support),
Composer 2, and Node.js 22.18+ with npm. Put `php`, `composer`, `node`, and `npm` on
your terminal's PATH. The project has also been tested locally with Node.js 26.

From the project directory:

```sh
composer install
php -r "file_exists('.env') || copy('.env.example', '.env');"
php artisan key:generate
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
php artisan migrate --no-interaction
npm ci
npm run build
```

These instructions are for a fresh checkout. Preserve an existing `.env` and
application key when using an existing database. SQLite data lives in
`database/database.sqlite`; that file and `.env` are excluded from Git.

Start the Laravel server:

```sh
php artisan serve
```

For frontend hot reload, run this in a second terminal:

```sh
npm run dev
```

Open the address printed by `artisan serve`, then visit one of these paths:

| Path                               | Screen                                    | Access                       |
| ---------------------------------- | ----------------------------------------- | ---------------------------- |
| `/songs`                           | Song library, including your saved drafts | Public; drafts require login |
| `/songs/midnight-platform`         | Demo song detail                          | Public                       |
| `/songs/midnight-platform/perform` | Demo performance view                     | Public                       |
| `/login`                           | Sign in                                   | Public                       |
| `/songs/import`                    | Import and review a song                  | Signed in                    |

### Development account

Self-registration is disabled. On a fresh local database, run the existing seeder
once to create a development account:

```sh
php artisan db:seed --no-interaction
```

- Email: `test@example.com`
- Password: `password`

The seeder is not idempotent: running it again with this account already present
will fail on the unique email constraint. These credentials are for local
development only.

## Importing songs

1. Sign in and open `/songs/import`.
2. Paste a chord chart or select a UTF-8 text file, up to 100,000 bytes.
3. Review the parsed sections, metadata, and warnings. Correct the source and
   preview it again if necessary.
4. Confirm your review and save the private draft.
5. Open the draft from your song library and enter its performance view.

Supported file extensions are `.txt`, `.cho`, `.chopro`, `.chordpro`, `.crd`, and
`.pro`. The importer handles common ChordPro directives, inline chords, chords above
lyrics, and section headings. For example, this fictional chart can be pasted
directly into the import screen:

```text
{title: Homeward Lights}
{artist: Bandbook demo}
{key: G}
{tempo: 96}

[Verse]
[G]Footsteps ring along the [D]street
[Em]Every window holds a [C]beat

[Chorus]
[C]Carry the [G]light
[D]All through the [G]night
```

Imports retain the original source alongside the reviewed content. A source URL
is a reference only: Bandbook does not fetch or scrape that page. PDFs, images,
Guitar Pro files, and structured guitar tablature are not supported. Import charts
you have permission to copy; check parser warnings before performing from a draft.

## Checks and tests

Generate frontend assets and Wayfinder route helpers before type checking a fresh
checkout:

```sh
npm run build
npm run check
npm run types:check
composer types:check
php artisan test --compact
node --test tests/Frontend/importFile.test.mjs tests/Frontend/performance.test.mjs
```

Laravel tests use an in-memory SQLite database. Tests for disabled two-factor
authentication are skipped. Frontend tests cover import file validation and local
performance navigation. `composer ci:check` runs the configured formatting, lint,
type, and Laravel test checks; run the Node tests separately as shown above.

## Code map

| Location                                        | Purpose                                          |
| ----------------------------------------------- | ------------------------------------------------ |
| `app/Actions/ParseSongChart.php`                | Chart parsing and normalization                  |
| `app/Http/Controllers/SongImportController.php` | Import screen and preview                        |
| `app/Http/Controllers/SongDraftController.php`  | Private draft persistence and display            |
| `app/Models/SongDraft.php`                      | Stored draft content and occurrences             |
| `app/Policies/SongDraftPolicy.php`              | Draft ownership authorization                    |
| `resources/js/pages/songs/`                     | Library, detail, import, and performance pages   |
| `resources/js/components/`                      | Reusable music and starter-kit UI components     |
| `resources/js/composables/usePerformance.ts`    | Local section and following state                |
| `resources/js/lib/demo-songs.json`              | Fictional demo library                           |
| `tests/Feature/SongImportTest.php`              | Parsing, storage, and access regression coverage |

Wayfinder generates `resources/js/actions`, `resources/js/routes`, and
`resources/js/wayfinder` during the frontend build. These directories are excluded
from Git and should not be edited manually.
