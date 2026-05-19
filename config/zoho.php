<?php

return [
    'client_id' => env('ZOHO_CLIENT_ID'),
    'client_secret' => env('ZOHO_CLIENT_SECRET'),
    'redirect_uri' => env('ZOHO_REDIRECT_URI', 'http://127.0.0.1:8000/zoho/oauth/callback'),
    'accounts_url' => env('ZOHO_ACCOUNTS_URL', 'https://accounts.zoho.com'),
    'voice_api_url' => env('ZOHO_VOICE_API_URL', 'https://voice.zoho.com/rest/json'),
    'sender_id' => env('ZOHO_VOICE_SENDER_ID', 'BOSCHMA'),
    'scopes' => [
        'ZohoVoice.sms.CREATE',
        'ZohoVoice.sms.READ',
        'ZohoVoice.users.READ',
        'ZohoVoice.calllogs.READ'
    ],
];
