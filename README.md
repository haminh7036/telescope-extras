# Telescope Extras

Small add-ons for [Laravel Telescope](https://github.com/laravel/telescope) 5.x. The package is installed alongside Telescope and does not modify it.

- **Query filter:** adds a search box to the *Queries* tab of a request, command or job. Use `%` as a wildcard, for example `select%orders`. Originally proposed in [laravel/telescope#1668](https://github.com/laravel/telescope/pull/1668).
- **Duplicate query highlighting:** highlights repeated SQL queries in the *Queries* tab with a warning background to surface duplicate executions. Originally proposed by @sumankathet51 in [laravel/telescope#1361](https://github.com/laravel/telescope/pull/1361).
- **Copy request as cURL:** adds a button to the *Request Details* screen to copy the request as an executable cURL command with method, headers, and payload. Originally proposed by @hbakouane in [laravel/telescope#1657](https://github.com/laravel/telescope/pull/1657) and @EhabAmawi in [laravel/telescope#1622](https://github.com/laravel/telescope/pull/1622).
- **Named binding fix:** with bindings like `:content1` and `:content10`, Telescope replaced `:content1` inside `:content10` and recorded broken SQL such as `'a'0`. Originally proposed in [laravel/telescope#1765](https://github.com/laravel/telescope/pull/1765).
- **XML HTTP client support:** parses XML request payloads and responses into arrays so they can be inspected, masked (`hideParameters`), and displayed as structured data in the dashboard instead of empty payloads or `HTML Response`. Originally proposed in [laravel/telescope#1758](https://github.com/laravel/telescope/pull/1758).

## Installation

```bash
composer require haminh7036/telescope-extras --dev
```

The query filter, duplicate query highlighting, and cURL copy button are enabled automatically.

To use the binding fix or XML HTTP client support, replace the corresponding watchers in `config/telescope.php`:

```php
'watchers' => [
    // Watchers\QueryWatcher::class => [
    \Haminh7036\TelescopeExtras\Watchers\QueryWatcher::class => [
        'enabled' => env('TELESCOPE_QUERY_WATCHER', true),
        'ignore_packages' => true,
        'ignore_paths' => [],
        'slow' => 100,
    ],

    // Watchers\ClientRequestWatcher::class => [
    \Haminh7036\TelescopeExtras\Watchers\ClientRequestWatcher::class => [
        'enabled' => env('TELESCOPE_CLIENT_REQUEST_WATCHER', true),
    ],
],
```

## How it works

Telescope ships its dashboard as a compiled bundle, so the filter cannot be added to its Vue components directly. Instead, a middleware in the `telescope` middleware group appends a small script and stylesheet to the dashboard page. Other Telescope responses, such as API calls and mail previews, are not changed. The script adds the search box and highlights duplicate queries once the Queries table is rendered, and appends the "Copy as cURL" button when viewing Request Details.

The script depends on Telescope's markup (`.card > ul.nav-pills`, and query rows linking to `/queries/{id}`). After upgrading Telescope, check that the box still appears on the Queries tab.

## Uninstall

Change any custom watchers in `config/telescope.php` back to Telescope's defaults (e.g. `Watchers\QueryWatcher::class`, `Watchers\ClientRequestWatcher::class`), then run `composer remove haminh7036/telescope-extras`.

## Testing

```bash
composer install
vendor/bin/phpunit
```

## License

MIT
