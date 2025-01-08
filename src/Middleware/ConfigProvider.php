<?php

declare(strict_types=1);

namespace Settermjd\Middleware;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencyConfig(),
        ];
    }

    public function getDependencyConfig(): array
    {
        return [
            'factories' => [
                RedirectToNewDomainMiddleware::class => RedirectToNewDomainMiddlewareFactory::class,
            ],
        ];
    }
}
