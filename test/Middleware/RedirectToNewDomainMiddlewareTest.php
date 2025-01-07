<?php

declare(strict_types=1);

namespace Settermjd\MiddlewareTest;

use Laminas\Diactoros\Response\EmptyResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Settermjd\Middleware\RedirectToNewDomainMiddleware;

class RedirectToNewDomainMiddlewareTest extends TestCase
{
    #[TestWith(['deploywithdockercompose.com'])]
    public function testRedirectsToNewDomainIfRequestComesFromOldDomain(string $requestedDomain): void
    {
        $invokedCount = $this->exactly(2);
        $log = $this->createMock(LoggerInterface::class);
        $log
            ->expects($invokedCount)
            ->method("debug")
            ->willReturnCallback(
                function ($parameters) use ($invokedCount, $requestedDomain) {
                    if ($invokedCount->numberOfInvocations() === 1) {
                        $this->assertSame(sprintf("received request from host: %s", $requestedDomain), $parameters);
                    }
                    if ($invokedCount->numberOfInvocations() === 2) {
                        $this->assertSame(
                            sprintf(
                                "redirecting to: %s", 
                                "https://deploywithdockercompose.webdevwithmatt.com/"
                            ), 
                            $parameters
                        );
                    }
                }
            );

        $middleware = new RedirectToNewDomainMiddleware(
            "deploywithdockercompose.com",
            "https://deploywithdockercompose.webdevwithmatt.com/",
            $log
        );

        $uri = $this->createMock(UriInterface::class);
        $uri
            ->expects($this->atMost(2))
            ->method("getHost")
            ->willReturn($requestedDomain);
        $request  = $this->createConfiguredStub(
            ServerRequestInterface::class,
            [
                "getUri" => $uri,
            ]
        );
        $response = $middleware->process($request, $this->createMock(RequestHandlerInterface::class));

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame("https://deploywithdockercompose.webdevwithmatt.com/", $response->getHeaderLine("location"));
        self::assertSame(301, $response->getStatusCode());
    }

    #[TestWith(['deploywithdockercompose.webdevwithmatt.com'])]
    #[TestWith(['webdevwithmatt.com'])]
    #[TestWith(['example.com'])]
    #[TestWith(['localhost'])]
    #[TestWith(['example.org'])]
    public function testDoesNotRedirectToNewDomainIfRequestDoesNotComeFromOldDomain(string $requestedDomain): void
    {
        $middleware = new RedirectToNewDomainMiddleware(
            "deploywithdockercompose.com",
            "https://deploywithdockercompose.webdevwithmatt.com/"
        );

        $uri = $this->createMock(UriInterface::class);
        $uri
            ->expects($this->atMost(2))
            ->method("getHost")
            ->willReturn($requestedDomain);
        $request = $this->createConfiguredStub(
            ServerRequestInterface::class,
            [
                "getUri" => $uri,
            ]
        );
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler
            ->expects($this->once())
            ->method("handle")
            ->willReturn(new EmptyResponse());

        $response = $middleware->process($request, $handler);

        self::assertInstanceOf(EmptyResponse::class, $response);
        self::assertSame(204, $response->getStatusCode());
    }

    #[TestWith(['deploywithdockercompose.webdevwithmatt.com'])]
    #[TestWith(['webdevwithmatt.com'])]
    #[TestWith(['example.com'])]
    #[TestWith(['localhost'])]
    #[TestWith(['example.org'])]
    public function testLogsRequestedDomainIfLoggerIsPresent(string $requestedDomain): void
    {
        $log = $this->createMock(LoggerInterface::class);
        $log
            ->expects($this->once())
            ->method("debug")
            ->with(
                sprintf("received request from host: %s", $requestedDomain)
            );

        $middleware = new RedirectToNewDomainMiddleware(
            "deploywithdockercompose.com",
            "https://deploywithdockercompose.webdevwithmatt.com/",
            $log
        );

        $uri = $this->createMock(UriInterface::class);
        $uri
            ->expects($this->atMost(2))
            ->method("getHost")
            ->willReturn($requestedDomain);
        $request = $this->createConfiguredStub(
            ServerRequestInterface::class,
            [
                "getUri" => $uri,
            ]
        );
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler
            ->expects($this->once())
            ->method("handle")
            ->willReturn(new EmptyResponse());

        $middleware->process($request, $handler);
    }
}
