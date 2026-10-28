# Contributing to Telescope Extras

To keep this package lean and fully compatible with future Laravel Telescope updates, please follow these architectural guidelines.

## Architecture Guidelines

- **UI Enhancements**:
  - Implement via injected frontend assets (`resources/dist/telescope-extras.js`, `resources/dist/telescope-extras.css`).
  - Do not attempt to modify Telescope's compiled Vue bundle directly. Keep DOM selectors resilient.

- **Watcher Extensions**:
  - Subclass Telescope's base watcher under `src/Watchers/`.
  - **Bugfixes** (e.g., binding replacements, composite key handling): Enabled by default without toggle options.
  - **Optional Features**: Must read flags from `$this->options` (configured by the application in `config/telescope.php`). Do not introduce separate configuration files.

- **Zero Core Mutation**:
  - Do not override Telescope's internal storage repositories (`DatabaseEntriesRepository`) or core service providers.

## Testing

Run the test suite before submitting a pull request:

```bash
composer install
vendor/bin/phpunit
```

All new watcher logic and bugfixes must include corresponding tests under `tests/`.

## Pull Requests

- Use English for all PR descriptions, commit messages, and code comments.
- Keep tone strictly neutral, factual, and constructive.
- If your contribution is based on an external PR or idea, credit the original author in the PR description.
- Synchronize `README.md` in the same PR if configuration steps change.
