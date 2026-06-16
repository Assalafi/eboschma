<?php

namespace App\Services;

use Twilio\Rest\Client;
use Twilio\TwiML\VoiceResponse;
use Twilio\Jwt\AccessToken;
use Twilio\Jwt\Grants\VoiceGrant;
use Illuminate\Support\Facades\Log;

class TwilioService
{
    protected Client $client;
    protected string $fromNumber;
    protected string $accountSid;

    public function __construct()
    {
        $this->accountSid = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $this->fromNumber = config('services.twilio.from');

        $this->client = new Client($this->accountSid, $token);
    }

    /**
     * Generate an Access Token for Twilio Client WebRTC.
     */
    public function generateClientToken(string $identity): ?string
    {
        $apiKey = config('services.twilio.api_key');
        $apiSecret = config('services.twilio.api_secret');
        $twimlAppSid = config('services.twilio.twiml_app_sid');

        if (!$apiKey || !$apiSecret || !$twimlAppSid) {
            Log::error('Twilio Client Token generation failed: Missing API Key, Secret, or TwiML App SID.');
            return null;
        }

        $token = new AccessToken(
            $this->accountSid,
            $apiKey,
            $apiSecret,
            3600,
            $identity
        );

        $voiceGrant = new VoiceGrant();
        $voiceGrant->setOutgoingApplicationSid($twimlAppSid);
        $voiceGrant->setIncomingAllow(true);

        $token->addGrant($voiceGrant);

        return $token->toJWT();
    }

    /**
     * Normalize any phone number to E.164 format.
     * Handles Nigerian local numbers: 07xxx, 08xxx, 09xxx → +234xxxxxxxxxx
     */
    protected function normalizePhone(string $phone, string $countryCode = '234'): string
    {
        $stripped = preg_replace('/[^\d+]/', '', trim($phone));

        // Already E.164
        if (preg_match('/^\+\d{10,15}$/', $stripped)) {
            return $stripped;
        }

        $digits = ltrim($stripped, '+');

        // Full country code already included (e.g. 2349064659803)
        if (str_starts_with($digits, $countryCode) && strlen($digits) >= 12) {
            return '+' . $digits;
        }

        // Local format with leading 0 (e.g. 09064659803)
        if (str_starts_with($digits, '0') && strlen($digits) >= 10) {
            return '+' . $countryCode . substr($digits, 1);
        }

        // Local without leading 0 (e.g. 9064659803)
        if (strlen($digits) >= 10) {
            return '+' . $countryCode . $digits;
        }

        return '+' . $digits;
    }

    /**
     * Send an outbound SMS message.
     */
    public function sendSmsV2(string $to, string $message, string $from = null, bool $isMms = false, string $mmsMediaFilePath = null): array
    {
        try {
            $normalizedTo = $this->normalizePhone($to);
            $params = [
                'from' => $from ?? $this->fromNumber,
                'body' => $message,
            ];

            $msg = $this->client->messages->create($normalizedTo, $params);

            Log::info("Twilio SMS sent to {$normalizedTo}: SID={$msg->sid}");
            return ['status' => 'success', 'sid' => $msg->sid];
        } catch (\Exception $e) {
            Log::error("Twilio SMS error to {$to}: " . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Initiate an outbound phone call (server-side REST API).
     * Twilio will hit the $twimlUrl to get call instructions.
     */
    public function makeCall(string $to, string $twimlUrl): array
    {
        try {
            $normalizedTo = $this->normalizePhone($to);
            $call = $this->client->calls->create(
                $normalizedTo,       // To (E.164)
                $this->fromNumber,   // From (must be verified Twilio number)
                ['url' => $twimlUrl]
            );

            Log::info("Twilio Call initiated to {$normalizedTo}: SID={$call->sid}");
            return ['status' => 'success', 'sid' => $call->sid, 'call_status' => $call->status];
        } catch (\Exception $e) {
            Log::error("Twilio Call error to {$to}: " . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Fetch call details by SID.
     */
    public function getCallDetails(string $callSid): ?object
    {
        try {
            return $this->client->calls($callSid)->fetch();
        } catch (\Exception $e) {
            Log::error("Twilio fetch call error [{$callSid}]: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate TwiML to say a message (for inbound or outbound call responses).
     */
    public static function sayTwiml(string $text, string $voice = 'alice'): string
    {
        $response = new VoiceResponse();
        $response->say($text, ['voice' => $voice]);
        return (string) $response;
    }

    /**
     * Generate TwiML to play hold music then say a message.
     */
    public static function holdTwiml(): string
    {
        $response = new VoiceResponse();
        $response->say('Thank you for calling BOSCHMA Support. Please hold while we connect you to an agent.', ['voice' => 'alice']);
        $response->play('https://com.twilio.music.classical.s3.amazonaws.com/ClockworkWaltz.mp3');
        return (string) $response;
    }

    /**
     * List recent SMS messages.
     */
    public function getSmsLogs(int $limit = 20): array
    {
        try {
            $messages = $this->client->messages->read([], $limit);
            return array_map(fn($m) => [
                'sid' => $m->sid,
                'to' => $m->to,
                'from' => $m->from,
                'body' => $m->body,
                'status' => $m->status,
                'date_sent' => $m->dateSent,
            ], $messages);
        } catch (\Exception $e) {
            Log::error('Twilio SMS logs error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * List recent call logs.
     */
    public function getCallLogs(int $limit = 20): array
    {
        try {
            $calls = $this->client->calls->read([], $limit);
            return array_map(fn($c) => [
                'sid' => $c->sid,
                'to' => $c->to,
                'from' => $c->from,
                'status' => $c->status,
                'duration' => $c->duration,
                'direction' => $c->direction,
                'start_time' => $c->startTime,
            ], $calls);
        } catch (\Exception $e) {
            Log::error('Twilio Call logs error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Validate that Twilio credentials are working.
     */
    public function validateCredentials(): bool
    {
        try {
            $this->client->api->v2010->accounts($this->accountSid)->fetch();
            return true;
        } catch (\Exception $e) {
            Log::error('Twilio credentials validation failed: ' . $e->getMessage());
            return false;
        }
    }
}
