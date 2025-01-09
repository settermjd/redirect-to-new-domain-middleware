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

/**
 * RedirectToNewDomainMiddleware performs a 301 redirect the current request
 * from one domain (oldDomain) to another (newDomain) if the current request is
 * for the old domain. It is designed to simplify moving sites from one domain
 * to another, especially in cases where the hosting provider does not provide
 * this functionality natively.
 */
final readonly class RedirectToNewDomainMiddleware implements MiddlewareInterface
{
    public function __construct(
        private string $oldDomain,
        private string $newDomain,
        private ?LoggerInterface $logger = null
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->logger
            ?->debug(sprintf("received request from host: %s", (string) $request->getUri()));

        if ($request->getUri()->getHost() === $this->oldDomain) {
            $newRequestUri = $request->getUri()->withHost($this->newDomain);
            $this->logger
                ?->debug(sprintf("redirecting to: %s", $newRequestUri));
            return new RedirectResponse($newRequestUri, 301);
        }

        return $handler->handle($request);
    }
}
