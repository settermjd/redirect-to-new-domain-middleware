<?php

declare(strict_types=1);

namespace Settermjd\MiddlewareTest;

use Fig\Http\Message\StatusCodeInterface;
use Laminas\Diactoros\Response\EmptyResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Laminas\Diactoros\Uri;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Settermjd\Middleware\RedirectToNewDomainMiddleware;

use function sprintf;

class RedirectToNewDomainMiddlewareTest extends TestCase
{
    private string $oldDomain = "deploywithdockercompose.com";
    private string $newDomain = "deploywithdockercompose.webdevwithmatt.com";

    #[TestWith(
        [
            "https://deploywithdockercompose.com", // The original request made
            "https://deploywithdockercompose.webdevwithmatt.com", // The redirected request
        ],
        "Test redirecting from one domain to another"
    )]
    #[TestWith(
        [
            "https://deploywithdockercompose.com/api/ping",
            "https://deploywithdockercompose.webdevwithmatt.com/api/ping",
        ],
        "Test redirecting from one domain to another with a path"
    )]
    #[TestWith(
        [
            "https://deploywithdockercompose.com?display=dark",
            "https://deploywithdockercompose.webdevwithmatt.com?display=dark",
        ],
        "Test redirecting from one domain to another with query parameters"
    )]
    #[TestWith(
        [
            "https://deploywithdockercompose.com/api/ping?display=dark",
            "https://deploywithdockercompose.webdevwithmatt.com/api/ping?display=dark",
        ],
        "Test redirecting from one domain to another with a path and query parameters"
    )]
    public function testRedirectsToNewDomainIfRequestComesFromOldDomain(
        string $originalRequest,
        string $newRequest
    ): void {
        $invokedCount = $this->exactly(2);
        $log          = $this->createMock(LoggerInterface::class);
        $log
            ->expects($invokedCount)
            ->method("debug")
            ->willReturnCallback(
                function ($parameters) use ($invokedCount, $originalRequest, $newRequest) {
                    if ($invokedCount->numberOfInvocations() === 1) {
                        $this->assertSame(sprintf("received request from host: %s", $originalRequest), $parameters);
                    }
                    if ($invokedCount->numberOfInvocations() === 2) {
                        $this->assertSame(
                            sprintf("redirecting to: %s", $newRequest),
                            $parameters
                        );
                    }
                }
            );

        $middleware = new RedirectToNewDomainMiddleware(
            oldDomain: $this->oldDomain,
            newDomain: $this->newDomain,
            logger: $log,
        );

        $uri      = new Uri($originalRequest);
        $request  = $this->createConfiguredStub(
            ServerRequestInterface::class,
            [
                "getUri" => $uri,
            ]
        );
        $response = $middleware->process($request, $this->createMock(RequestHandlerInterface::class));

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame($newRequest, $response->getHeaderLine("location"));
        self::assertSame(StatusCodeInterface::STATUS_MOVED_PERMANENTLY, $response->getStatusCode());
    }

    #[TestWith(['https://deploywithdockercompose.webdevwithmatt.com'])]
    #[TestWith(['https://webdevwithmatt.com'])]
    #[TestWith(['https://example.com'])]
    #[TestWith(['https://localhost'])]
    #[TestWith(['https://example.org'])]
    public function testDoesNotRedirectToNewDomainIfRequestDoesNotComeFromOldDomain(string $originalRequest): void
    {
        $middleware = new RedirectToNewDomainMiddleware(
            oldDomain: $this->oldDomain,
            newDomain: $this->newDomain,
        );

        $uri     = new Uri($originalRequest);
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

    #[TestWith(['https://deploywithdockercompose.webdevwithmatt.com'])]
    #[TestWith(['https://webdevwithmatt.com'])]
    #[TestWith(['https://example.com'])]
    #[TestWith(['https://localhost'])]
    #[TestWith(['https://example.org'])]
    public function testLogsRequestedDomainIfLoggerIsPresent(string $originalRequest): void
    {
        $log = $this->createMock(LoggerInterface::class);
        $log
            ->expects($this->once())
            ->method("debug")
            ->with(
                sprintf("received request from host: %s", $originalRequest)
            );

        $middleware = new RedirectToNewDomainMiddleware(
            oldDomain: $this->oldDomain,
            newDomain: $this->newDomain,
            logger: $log,
        );

        $uri     = new Uri($originalRequest);
        $request = $this->createConfiguredStub(ServerRequestInterface::class, [
            "getUri" => $uri,
        ]);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler
            ->expects($this->once())
            ->method("handle")
            ->willReturn(new EmptyResponse());

        $middleware->process($request, $handler);
    }

    #[TestWith([
        StatusCodeInterface::STATUS_MOVED_PERMANENTLY,
        StatusCodeInterface::STATUS_MOVED_PERMANENTLY,
    ])]
    #[TestWith([
        StatusCodeInterface::STATUS_FOUND,
        StatusCodeInterface::STATUS_FOUND,
    ])]
    #[TestWith([
        StatusCodeInterface::STATUS_SEE_OTHER,
        StatusCodeInterface::STATUS_MOVED_PERMANENTLY,
    ])]
    public function testCanSetRedirectStatusCodeTo301Or302(int $desiredStatus, int $actualStatus): void
    {
        $middleware = new RedirectToNewDomainMiddleware(
            oldDomain: $this->oldDomain,
            newDomain: $this->newDomain,
            redirectStatus: $desiredStatus,
        );

        $uri     = new Uri("https://deploywithdockercompose.com/api/ping?display=dark");
        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->method("getUri")
            ->willReturn($uri);
        $response = $middleware->process($request, $this->createMock(RequestHandlerInterface::class));

        self::assertSame($actualStatus, $response->getStatusCode());
    }
}
