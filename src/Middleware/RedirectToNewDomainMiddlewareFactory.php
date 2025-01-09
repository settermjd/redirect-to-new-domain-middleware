<?php

declare(strict_types=1);

namespace Settermjd\Middleware;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Settermjd\Middleware\Exception\InvalidConfigurationException;

/**
 * RedirectToNewDomainMiddlewareFactory instantiates a new
 * RedirectToNewDomainMiddleware object using environment variables first, if
 * they exist, and the application's configuration if not.
 */
final readonly class RedirectToNewDomainMiddlewareFactory
{
    public function __invoke(ContainerInterface $container): RedirectToNewDomainMiddleware
    {
        $oldDomain      = $_ENV["OLD_DOMAIN"] ?? "";
        $newDomain      = $_ENV["NEW_DOMAIN"] ?? "";
        $redirectStatus = $_ENV["REDIRECT_STATUS"] ?? RedirectToNewDomainMiddleware::DEFAULT_STATUS;

        if ($container->has("config")) {
            $config         = $container->get("config");
            $oldDomain      = $config["redirect-to-new-domain-middleware"]["old"] ?? null;
            $newDomain      = $config["redirect-to-new-domain-middleware"]["new"] ?? null;
            $redirectStatus = $config["redirect-to-new-domain-middleware"]["status"] ?? $redirectStatus;

            if ($oldDomain === null || $newDomain === null) {
                throw new InvalidConfigurationException(
                    "Configuration for RedirectToNewDomainMiddleware is missing or incomplete"
                );
            }
        }

        return new RedirectToNewDomainMiddleware(
            oldDomain: $oldDomain,
            newDomain: $newDomain,
            redirectStatus: $redirectStatus,
            logger: $container->get(LoggerInterface::class) ?? null
        );
    }
}
