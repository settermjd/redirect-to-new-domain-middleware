<?php

declare(strict_types=1);

namespace Settermjd\MiddlewareTest;

use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Settermjd\Middleware\Exception\InvalidConfigurationException;
use Settermjd\Middleware\RedirectToNewDomainMiddleware;
use Settermjd\Middleware\RedirectToNewDomainMiddlewareFactory;

class RedirectToNewDomainMiddlewareFactoryTest extends TestCase
{
    public function testCanInstantiateMiddlewareWithConfigDataWhenSet(): void
    {
        $factory   = new RedirectToNewDomainMiddlewareFactory();
        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects($this->once())
            ->method("has")
            ->with("config")
            ->willReturn(true);
        $container
            ->expects($this->once())
            ->method("get")
            ->with("config")
            ->willReturn([
                "redirect-to-new-domain-middleware" => [
                    "old" => "deploywithdockercompose.com",
                    "new" => "https://deploywithdockercompose.webdevwithmatt.com",
                ],
            ]);
        $middleware = $factory($container);

        self::assertInstanceOf(RedirectToNewDomainMiddleware::class, $middleware);
    }

    #[TestWith(
        [
            [],
        ]
    )]
    #[TestWith(
        [
            [
                "redirect-to-new-domain-middleware" => [
                    "new" => "https://deploywithdockercompose.webdevwithmatt.com",
                ],
            ],
        ]
    )]
    #[TestWith(
        [
            [
                "redirect-to-new-domain-middleware" => [
                    "old" => "deploywithdockercompose.com",
                ],
            ],
        ]
    )]
    #[TestWith(
        [
            [
                "redirect-to-new-domain-middleware" => [],
            ],
        ]
    )]
    public function testThrowsExceptionWhenConfigKeyOrRequiredConfigElementsAreMissing(array $config): void
    {
        $this->expectException(InvalidConfigurationException::class);

        $factory   = new RedirectToNewDomainMiddlewareFactory();
        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects($this->once())
            ->method("has")
            ->with("config")
            ->willReturn(true);
        $container
            ->expects($this->once())
            ->method("get")
            ->with("config")
            ->willReturn($config);
        $factory($container);
    }

    public function testCanInstantiateMiddlewareWithEnvironmentVariablesWhenSet(): void
    {
        $_ENV["OLD_DOMAIN"] = "deploywithdockercompose.com";
        $_ENV["NEW_DOMAIN"] = "https://deploywithdockercompose.webdevwithmatt.com";

        $factory   = new RedirectToNewDomainMiddlewareFactory();
        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects($this->once())
            ->method("has")
            ->with("config")
            ->willReturn(false);

        $middleware = $factory($container);

        self::assertInstanceOf(RedirectToNewDomainMiddleware::class, $middleware);
    }
}
