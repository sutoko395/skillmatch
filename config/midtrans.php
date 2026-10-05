<?php

return [
    'server_key' => env('MIDTRANS_SERVER_KEY'),
    'client_key' => env('MIDTRANS_CLIENT_KEY'), // Not needed by hosted Snap redirect; reserved for future embedded checkout.
    'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
];
