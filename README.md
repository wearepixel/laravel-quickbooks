# Laravel QuickBooks Client

[![Latest Stable Version](https://img.shields.io/packagist/v/wearepixel/laravel-quickbooks.svg)](https://packagist.org/packages/wearepixel/laravel-quickbooks)
[![Total Downloads](https://img.shields.io/packagist/dt/wearepixel/laravel-quickbooks.svg)](https://packagist.org/packages/wearepixel/laravel-quickbooks)
[![License](https://img.shields.io/packagist/l/wearepixel/laravel-quickbooks.svg)](https://packagist.org/packages/wearepixel/laravel-quickbooks)
[![PHP Version](https://img.shields.io/packagist/dependency-v/wearepixel/laravel-quickbooks/php.svg)](https://packagist.org/packages/wearepixel/laravel-quickbooks)

A Laravel package wrapping the [QuickBooks PHP SDK](https://github.com/intuit/QuickBooks-V3-PHP-SDK). Provides OAuth 2.0 authentication, automatic token management, and access to the QuickBooks Online API.

## Compatibility

| Laravel | PHP  | Package |
|---------|------|---------|
| 12.x    | ^8.3 | ^1.0    |
| 11.x    | ^8.3 | ^1.0    |
| 10.x    | ^8.3 | ^1.0    |

## Installation

1. Install the package:

```bash
composer require wearepixel/laravel-quickbooks
```

2. Run the migration to create the `quickbooks_tokens` table:

```bash
php artisan migrate
```

The package uses Laravel's [auto-discovery](https://laravel.com/docs/packages#package-discovery), so no manual provider registration is needed.

## Configuration

### 1. Add the trait to your User model

```php
use Wearepixel\QuickBooks\HasQuickBooksToken;

class User extends Authenticatable
{
    use HasQuickBooksToken;
}
```

If your User model is not `App\Models\User`, publish the config and update the `user.model` value.

### 2. Set your environment variables

```bash
QUICKBOOKS_CLIENT_ID=<your client id>
QUICKBOOKS_CLIENT_SECRET=<your client secret>
```

Optional:

```bash
QUICKBOOKS_API_URL=<Development|Production>  # Defaults based on APP_ENV
QUICKBOOKS_DEBUG=<true|false>                # Defaults to APP_DEBUG
```

### 3. Publish config and views (optional)

```bash
php artisan vendor:publish --tag=quickbooks-config
php artisan vendor:publish --tag=quickbooks-views
```

## Usage

First, direct users to `/quickbooks/connect` to authorize their QuickBooks account. Once connected, you can access the API:

```php
$quickbooks = app(\Wearepixel\QuickBooks\Client::class);

// Get company info
$company = $quickbooks->getDataService()->getCompanyInfo();

// Access reports
$reportService = $quickbooks->getReportService();
```

You can call any of the resources documented in the [QuickBooks PHP SDK](https://intuit.github.io/QuickBooks-V3-PHP-SDK/quickstart.html).

## Middleware

Protect routes that require a QuickBooks connection using the `quickbooks` middleware. Users without a valid token will be redirected to the connect page.

```php
Route::get('quickbooks/invoices', InvoiceController::class)
     ->middleware('quickbooks');
```

## Testing

```bash
./vendor/bin/pest
```

## License

MIT
