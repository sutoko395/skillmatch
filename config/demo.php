<?php

return [
    'enabled' => (bool) env('DEMO_ACCOUNTS_ENABLED', false),
    'organizer_email' => env('DEMO_ORGANIZER_EMAIL', 'eo@demo.test'),
    'volunteer_email' => env('DEMO_VOLUNTEER_EMAIL', 'vol@demo.test'),
    'password' => env('DEMO_ACCOUNT_PASSWORD'),
];
