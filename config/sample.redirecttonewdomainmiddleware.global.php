<?php

use Fig\Http\Message\StatusCodeInterface;

return [
    "redirect-to-new-domain-middleware" => [
        /**
         * This is the old domain that is being deprecated or is to be redirected from.
         * Replace this with the applicable domain name for your application.
         */
        "old" => "example.com",

        /**
         * This is the new domain, which requests are to be redirected to.
         * Replace this with the applicable domain name for your application.
         */
        "new" => "example.org",

        /**
         * This is the type of redirect, which can be set to either 301 (the
         * default) or 302.
         */
        "status" => StatusCodeInterface::STATUS_MOVED_PERMANENTLY,
    ],
];
