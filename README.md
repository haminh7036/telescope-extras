# Telescope Extras

Small add-ons for [Laravel Telescope](https://github.com/laravel/telescope) 5.x, installed next to it — no fork, nothing in `vendor/laravel/telescope` is changed.

- **Query filter** — a search box on the *Queries* tab of a request/command/job's related entries. `%` works as a wildcard (`select%orders`). Proposed upstream in [laravel/telescope#1668](https://github.com/laravel/telescope/pull/1668).
- **Named binding fix** — `:content1` no longer gets replaced inside `:content10`, which produced SQL like `'a'0` in the recorded query. Proposed upstream in [laravel/telescope#1765](https://github.com/laravel/telescope/pull/1765).

## Installation

```bash
composer require haminh7036/telescope-extras --dev
```

The query filter is enabled automatically.

To use the binding fix, swap the query watcher in `config/telescope.php`:

```php
'watchers' => [
    // Watchers\QueryWatcher::class => [
    \Haminh7036\TelescopeExtras\Watchers\QueryWatcher::class => [
        'enabled' => env('TELESCOPE_QUERY_WATCHER', true),
        'ignore_packages' => true,
        'ignore_paths' => [],
        'slow' => 100,
    ],
],
```

## How it works

Telescope inlines its compiled dashboard JS, so the filter is added from the outside: a middleware appended to the `telescope` middleware group injects a small script and stylesheet into the dashboard page (route `telescope` only — API responses and mail previews are left alone). The script watches the DOM and adds the box when the related Queries table is rendered.

Because it relies on Telescope's markup (`.card > ul.nav-pills`, query rows linking to `/queries/{id}`), check the Queries tab after upgrading Telescope.

## Uninstall

Restore `Watchers\QueryWatcher::class` in `config/telescope.php`, then `composer remove haminh7036/telescope-extras`.

## Testing

```bash
composer install
vendor/bin/phpunit
```

## License

MIT
