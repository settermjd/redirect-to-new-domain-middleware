# Redirect to new domain middleware

This is a small piece of [PSR-15 (HTTP Server Request Handlers)][psr-15] middleware that redirects a request from one domain to a new one; such as an old one that's being deprecated to a new one when a site is being re-hosted.

## Prerequisites

To use the package, you'll need the following:

- PHP 8.3 or above with the following extensions
  - ext-ctype
  - ext-dom
  - ext-filter
  - ext-json
  - ext-libxml
  - ext-mbstring
  - ext-phar
  - ext-simplexml
  - ext-tokenizer
  - ext-xml
  - ext-xmlwriter

## Getting Started

### With Mezzio

To use the project, in a Mezzio application, for all routes that need the functionality, load the middleware as early in the route's pipeline as possible. 
This will ensure that the redirect happens before any other functionality is invoked.

For example:

```php
[
    'path'            => '/',
    'middleware'      => [
        Settermjd\Middleware\RedirectToNewDomainMiddleware::class,
        App\Handler\HomePageHandler::class,
    ]
    'allowed_methods' => ['GET'],
],
```

You should not need to do anything else, as RedirectToNewDomainMiddleware will be registered with the application's DI container via the package's [ConfigProvider][config-provider] class, which is automatically loaded when the package is installed into a Mezzio application.

## Contributing

If you want to contribute to the project, whether you have found issues with it or just want to improve it, here's how:

- [Issues][issues]: ask questions and submit your feature requests, bug reports, etc
- [Pull requests][prs]: send your improvements

## Did You Find The Project Useful?

If the project was useful and you want to say thank you and/or support its active development, here's how:

- Add a GitHub Star to the project
- Write an interesting article about the project wherever you blog

## Disclaimer

No warranty expressed or implied. Software is as is.

<!-- Page links -->
[config-provider]: https://matthewsetter.com/using-configproviders/
[psr-15]: https://www.php-fig.org/psr/psr-15/
[issues]: https://github.com/settermjd/redirect-to-new-domain-middleware/issues 
[prs]: https://github.com/settermjd/redirect-to-new-domain-middleware/issues