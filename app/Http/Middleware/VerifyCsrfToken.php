<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     * Twilio webhook routes must be excluded because Twilio POSTs to them
     * without a CSRF token.
     *
     * @var array<int, string>
     */
    protected $except = [
        'twilio/voice/inbound',
        'twilio/voice/client-outbound',
        'twilio/webhook/call-status',
        'api/twilio/webhook/call-status',
    ];
}
