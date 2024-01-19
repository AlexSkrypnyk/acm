<p align="center">
  <a href="" rel="noopener">
  <img width=200px height=200px src="https://placehold.jp/000000/ffffff/200x200.png?text=API+credentials+manager&css=%7B%22border-radius%22%3A%22%20100px%22%7D" alt="API credentials manager"></a>
</p>

<h1 align="center">API credentials manager</h1>


<div align="center">

[![GitHub Issues](https://img.shields.io/github/issues/AlexSkrypnyk/acm.svg)](https://github.com/AlexSkrypnyk/acm/issues)
[![GitHub Pull Requests](https://img.shields.io/github/issues-pr/AlexSkrypnyk/acm.svg)](https://github.com/AlexSkrypnyk/acm/pulls)
[![CircleCI](https://circleci.com/gh/AlexSkrypnyk/acm.svg?style=shield)](https://circleci.com/gh/AlexSkrypnyk/acm)
![GitHub release (latest by date)](https://img.shields.io/github/v/release/AlexSkrypnyk/acm)
![LICENSE](https://img.shields.io/github/license/AlexSkrypnyk/acm)
![Renovate](https://img.shields.io/badge/renovate-enabled-green?logo=renovatebot)

![Drupal 9](https://img.shields.io/badge/Drupal-9-blue.svg) ![Drupal 10](https://img.shields.io/badge/Drupal-10-blue.svg)

</div>

---

<p align="center">Drupal module to manage API credentials between environments.</p>

## Use case

## Features
- Flexible API to define environments, endpoints and credentials fields.
- Integration with [Encrypt](https://www.drupal.org/project/encrypt) module (and [Key](https://www.drupal.org/project/key) module by extent).

## Local development

Provided that you have PHP installed locally, you can develop an extension using
the provided scripts.

### Build

Run `.devtools/build-codebase.sh` (or `ahoy build-codebase`
if [Ahoy](https://github.com/ahoy-cli/ahoy) is installed) to start inbuilt PHP
server locally and run the same commands as in CI, plus installing a site and
your extension automatically.

### Code linting

Run tools individually (or `ahoy lint` to run all tools
if [Ahoy](https://github.com/ahoy-cli/ahoy) is installed) to lint your code
according to
the [Drupal coding standards](https://www.drupal.org/docs/develop/standards).

```
cd build

vendor/bin/phpcs
vendor/bin/phpstan
vendor/bin/rector --clear-cache --dry-run
vendor/bin/phpmd . text phpmd.xml
vendor/bin/twigcs
```

- PHPCS config: [`phpcs.xml`](phpcs.xml)
- PHPStan config: [`phpstan.neon`](phpstan.neon)
- PHPMD config: [`phpmd.xml`](phpmd.xml)
- Rector config: [`rector.php`](rector.php)
- TwigCS config: [`.twig_cs.php`](.twig_cs.php)

### Tests

Run `.devtools/test.sh` (or `ahoy test`
if [Ahoy](https://github.com/ahoy-cli/ahoy) is installed) to run all test for
your extension.

### Browsing SQLite database

To browse the contents of created SQLite database
(located at `/tmp/site_[EXTENSION_NAME].sqlite`),
use [DB Browser for SQLite](https://sqlitebrowser.org/).
