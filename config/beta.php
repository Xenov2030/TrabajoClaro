<?php

return [
    'version' => env('APP_RELEASE', '2026.10-beta.2'),
    'feedback_retention_days' => 90,
    'trusted_proxies' => array_values(array_filter(array_map('trim', explode(',', env('TRUSTED_PROXIES', ''))))),
];
