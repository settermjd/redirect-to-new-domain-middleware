<?php

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
        "new" => "https://example.org",
    ],
];
