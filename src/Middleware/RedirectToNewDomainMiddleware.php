<?php

declare(strict_types=1);

namespace Settermjd\Middleware;

use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

use function sprintf;

final readonly class RedirectToNewDomainMiddleware implements MiddlewareInterface
{
    public function __construct(
        private string $oldDomain,
        private string $newDomain,
        private ?LoggerInterface $logger = null
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->logger
            ?->debug(sprintf("received request from host: %s", $request->getUri()->getHost()));

        if ($request->getUri()->getHost() === $this->oldDomain) {
            $this->logger
                ?->debug(sprintf("redirecting to: %s", $this->newDomain));
            return new RedirectResponse($this->newDomain, 301);
        }

        return $handler->handle($request);
    }
}
