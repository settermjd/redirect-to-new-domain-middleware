<?php

declare(strict_types=1);

namespace Settermjd\Middleware;

use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

use function in_array;
use function sprintf;

/**
 * RedirectToNewDomainMiddleware performs a 301 redirect the current request
 * from one domain ($oldDomain) to another ($newDomain) if the current request is
 * for the old domain. It is designed to simplify moving sites from one domain
 * to another, especially in cases where the hosting provider does not provide
 * this functionality natively.
 */
final readonly class RedirectToNewDomainMiddleware implements MiddlewareInterface
{
    public const int DEFAULT_STATUS = 301;

    public function __construct(
        /**
         * This is the host portion of the route that was originally requested,
         * e.g., deploywithdockercompose.com, hollows.org, or rspca.org.au.
         */
        private string $oldDomain,
        /**
         * This is the host portion of the route that the request will be
         * redirected to, e.g., asrc.org.au, wwf.org.au, or
         * blackdoginstitute.org.au.
         */
        private string $newDomain,
        /**
         * The status code to redirect with.
         * Allowed values are 301 and 302.
         */
        private int $redirectStatus = self::DEFAULT_STATUS,
        /**
         * If you want to perform basic instrumentation, provide this parameter.
         */
        private ?LoggerInterface $logger = null
    ) {
    }

    /**
     * process checks the host portion of the current request. If it matches the
     * old domain ($oldDomain) the host is set to the new domain ($newDomain)
     * and the user is redirected to the revised URI.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->logger
            ?->debug(sprintf("received request from host: %s", (string) $request->getUri()));

        if ($request->getUri()->getHost() === $this->oldDomain) {
            $newRequestUri = $request->getUri()->withHost($this->newDomain);
            $this->logger
                ?->debug(sprintf("redirecting to: %s", $newRequestUri));
            return new RedirectResponse(
                $newRequestUri,
                ! in_array($this->redirectStatus, [301, 302])
                    ? self::DEFAULT_STATUS
                    : $this->redirectStatus
            );
        }

        return $handler->handle($request);
    }
}
