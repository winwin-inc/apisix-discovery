<?php // -*- php -*-

use function kuiper\helper\env;

return [
    "application" => [
        'apisix' => [
            'admin_uri' => env("APISIX_ADMIN_URI"),
            'api_key' => env('APISIX_API_KEY'),
            'upstream_template' => [
                "type" => "roundrobin",
                "retries" => 2,
                "checks" => [
                    "active" => [
                        "http_path" => "/status.html",
                        "healthy" => [
                            "interval" => 2,
                            "successes" => 1,
                        ],
                        "unhealthy" => [
                            "interval" => 1,
                            "http_failures" => 2,
                        ],
                    ],
                    "passive" => [
                        "healthy" => [
                            "http_statuses" => [
                                200,
                                201,
                            ],
                            "successes" => 3,
                        ],
                        "unhealthy" => [
                            "http_failures" => 3,
                            "tcp_failures" => 3,
                        ],
                    ],
                ],
            ]
        ]
    ]
];