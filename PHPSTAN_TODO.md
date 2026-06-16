# PHPStan / Larastan TODO

- This repository is an overlay and does not contain the generated Laravel app's
  `composer.json`, so `larastan/larastan` could not be added to `require-dev`
  here without creating an overlay manifest that would overwrite the app
  manifest during install.
- After applying the overlay to the generated Laravel app, run:

  ```bash
  composer require --dev larastan/larastan
  vendor/bin/phpstan analyse
  ```

- No `@phpstan-ignore` suppressions were added in this pass.
