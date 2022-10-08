<!--
![Build](https://github.com/cgauge/laravel-bref-adapter/workflows/Tests/badge.svg)
[![Code Coverage](https://scrutinizer-ci.com/g/cgauge/laravel-bref-adapter/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/cgauge/laravel-cognito-provider/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/cgauge/laravel-bref-adapter/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/cgauge/laravel-cognito-provider/?branch=master)
-->

# Laravel Bref Adapter 🔌

This library provides a [Bref Adapter](https://bref.sh) for Laravel 

# Installation

```bash
composer require customergauge/bref
```

# Configuration

We need to add `\CustomerGauge\Bref\Helpers\StorageDirectories::create($app);` to the `bootstrap/app.php` file.
This will ensure that the storage directories are created before the application is booted.

The following environment variables will configure Laravel to use the appropriate folders:

```dotenv
APP_SERVICES_CACHE: /tmp/laravel-bref-adapter/storage/cache/services.php
APP_PACKAGES_CACHE: /tmp/laravel-bref-adapter/storage/cache/packages.php
APP_ROUTES_CACHE: /tmp/laravel-bref-adapter/storage/cache/routes.php
VIEW_COMPILED_PATH: /tmp/laravel-bref-adapter/storage/framework/views
```

# Usage


