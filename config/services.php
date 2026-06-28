<?php

return [

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel'              => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    // ------------------------------
    // FONNTE WHATSAPP API CONFIG
    // ------------------------------
    'fonnte' => [
        'token'     => env('FONNTE_TOKEN'),
        'url'       => env('FONNTE_URL', 'https://api.fonnte.com/send'),
        'phone_to'  => env('NOTIFICATION_PHONE'),
    ],

    // ------------------------------
    // GOOGLE API CONFIG
    // ------------------------------
    'google' => [
        'client_id'     => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect'      => env('GOOGLE_REDIRECT_URI'),
        'app_name'      => env('GOOGLE_APP_NAME'),
    ],

];