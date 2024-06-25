# Wearepixel's Laravel QuickBooks Client

[![Latest Stable Version](https://poser.pugx.org/wearepixel/laravel-quickbooks/v/stable)](https://packagist.org/packages/wearepixel/laravel-quickbooks)
[![Latest Unstable Version](https://poser.pugx.org/wearepixel/laravel-quickbooks/v/unstable)](https://packagist.org/packages/wearepixel/laravel-quickbooks)
[![Total Downloads](https://poser.pugx.org/wearepixel/laravel-quickbooks/downloads)](https://packagist.org/packages/wearepixel/laravel-quickbooks)
[![License](https://poser.pugx.org/wearepixel/laravel-quickbooks/license)](https://packagist.org/packages/wearepixel/laravel-quickbooks)

PHP client wrapping the [QuickBooks PHP SDK](https://github.com/intuit/QuickBooks-V3-PHP-SDK).

We solely use [Laravel](https://www.laravel.com) for our applications, so this package is written with Laravel in mind. If there is a request from the community to split this package into 2 parts to allow it to be used outside of Laravel, then we will consider doing that work.

## Build Status

| Branch | Status | Coverage | Code Quality |
| ------ | :----: | :------: | :----------: |
| Master | [![Build Status](https://github.com/wearepixel/laravel-quickbooks/workflows/CI/badge.svg?branch=master)](https://github.com/wearepixel/laravel-quickbooks/workflows/CI/badge.svg?branch=master) | [![Code Coverage](https://scrutinizer-ci.com/g/wearepixel/laravel-quickbooks/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/wearepixel/laravel-quickbooks/?branch=master) | [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/wearepixel/laravel-quickbooks/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/wearepixel/laravel-quickbooks/?branch=main) |

## Installation

1. Install QuickBooks PHP Client:

```bash
$ composer require wearepixel/laravel-quickbooks
```

2. Run our migration to install the `quickbooks_tokens` table:

```bash
$ php artisan migrate --package=wearepixel/laravel-quickbooks
```

The package uses the [auto registration feature](https://laravel.com/docs/packages#package-discovery) of Laravel.

## Configuration

1. You will need a ```quickBooksToken``` relationship on your ```User``` model.  There is a trait named ```Wearepixel\QuickBooks\HasQuickBooksToken```, which you can include on your ```User``` model, which will setup the relationship. To do this implement the following:

Add ```use Wearepixel\QuickBooks\HasQuickBooksToken;``` to your service container at the top of User.php
and also add the trait within the class. For example:

```php
class User extends Authenticatable
{
    use Notifiable, HasQuickBooksToken;
```

**NOTE: If your ```User``` model is not ```App\Models\User```, then you will need to configure the path in the ```configs/quickbooks.php```.**

2. Add the appropriate values to your ```.env```

    #### Minimal Keys
    ```bash
    QUICKBOOKS_CLIENT_ID=<client id given by QuickBooks>
    QUICKBOOKS_CLIENT_SECRET=<client secret>
    ```

    #### Optional Keys
    ```bash
    QUICKBOOKS_API_URL=<Development|Production> # Defaults to App's env value
    QUICKBOOKS_DEBUG=<true|false>               # Defaults to App's debug value
    ```

3. _[Optional]_ Publish configs & views

    #### Config
    A configuration file named ```quickbooks.php``` can be published to ```config/``` by running...

    ```bash
    php artisan vendor:publish --tag=quickbooks-config
    ```

    #### Views
    View files can be published by running...

    ```bash
    php artisan vendor:publish --tag=quickbooks-views
    ```

## Usage

Here is an example of getting the company information from QuickBooks:

### NOTE: Before doing these commands, go to your connect route (default: /quickbooks/connect) to get a QuickBooks token for your user

```php
php artisan tinker
Psy Shell v0.8.17 (PHP 7.1.14 — cli) by Justin Hileman
>>> Auth::logInUsingId(1)
=> App\Models\User {#1668
     id: 1,
     // Other keys removed for example
   }
>>> $quickbooks = app('Wearepixel\QuickBooks\Client') // or app('QuickBooks')
=> Wearepixel\QuickBooks\Client {#1613}
>>> $quickbooks->getDataService()->getCompanyInfo();
=> QuickBooksOnline\API\Data\IPPCompanyInfo {#1673
     +CompanyName: "Sandbox Company_US_1",
     +LegalName: "Sandbox Company_US_1",
     // Other properties removed for example
   }
>>>
```

You can call any of the resources as documented [in the SDK](https://intuit.github.io/QuickBooks-V3-PHP-SDK/quickstart.html).

## Middleware

If you have routes that will be dependent on the user's account having a usable QuickBooks OAuth token, there is an included middleware ```Wearepixel\QuickBooks\Laravel\Filter``` that gets registered as ```quickbooks``` that will ensure the account is linked and redirect them to the `connect` route if needed.

Here is an example route definition:

```php
Route::view('some/route/needing/quickbooks/token/before/using', 'some.view')
     ->middleware('quickbooks');
```
