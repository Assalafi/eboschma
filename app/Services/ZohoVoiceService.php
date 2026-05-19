<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ZohoVoiceService
{
    protected $baseUrl;
    protected $clientId;
    protected $clientSecret;
    protected $accountsUrl;

    public function __construct()
    {
        $this->baseUrl = config('zoho.voice_api_url');
        $this->clientId = config('zoho.client_id');
        $this->clientSecret = config('zoho.client_secret');
        $this->accountsUrl = config('zoho.accounts_url');
    }

    /**
     * Get the active access token, checking for expiration and refreshing if needed.
     */
    public function getAccessToken()
    {
        $tokenRecord = DB::table('zoho_tokens')->orderBy('id', 'desc')->first();

        if (!$tokenRecord) {
            Log::warning('Zoho Voice: No access token found in database. Please authenticate.');
            return null;
        }

        // Check if token has expired (with 2 minutes buffer)
        $expiryTime = Carbon::parse($tokenRecord->expires_at);
        if ($expiryTime->subMinutes(2)->isPast()) {
            return $this->refreshAccessToken($tokenRecord->refresh_token);
        }

        return $tokenRecord->access_token;
    }

    /**
     * Refresh the access token using the refresh token.
     */
    public function refreshAccessToken($refreshToken)
    {
        if (empty($refreshToken)) {
            Log::error('Zoho Voice: Cannot refresh access token. Refresh token is empty.');
            return null;
        }

        try {
            $response = Http::asForm()->post("{$this->accountsUrl}/oauth/v2/token", [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'grant_type' => 'refresh_token',
                'refresh_token' => $refreshToken,
            ]);

            $data = $response->json();

            if ($response->successful() && isset($data['access_token'])) {
                $expiresIn = $data['expires_in'] ?? 3600;

                // Update token in DB
                DB::table('zoho_tokens')->updateOrInsert(
                    ['id' => 1], // Always keep a single active record for unified auth
                    [
                        'access_token' => $data['access_token'],
                        'refresh_token' => $data['refresh_token'] ?? $refreshToken, // reuse old if new is not provided
                        'expires_at' => now()->addSeconds($expiresIn),
                        'updated_at' => now(),
                    ]
                );

                Log::info('Zoho Voice: Access token refreshed successfully.');
                return $data['access_token'];
            } else {
                Log::error('Zoho Voice: Failed to refresh access token: ' . json_encode($data));
                return null;
            }
        } catch (\Exception $e) {
            Log::error('Zoho Voice: Exception occurred during token refresh: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Perform general HTTP request helper.
     */
    protected function makeRequest($method, $endpoint, $data = [], $headers = [])
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            throw new \Exception('Zoho Voice access token not available.');
        }

        $url = "{$this->baseUrl}/{$endpoint}";
        $defaultHeaders = array_merge([
            'Authorization' => 'Zoho-oauthtoken ' . $accessToken,
            'Accept' => 'application/json',
        ], $headers);

        $request = Http::withHeaders($defaultHeaders);

        if (strtolower($method) === 'get') {
            $response = $request->get($url, $data);
        } else {
            $response = $request->post($url, $data);
        }

        return $response->json();
    }

    /**
     * Send outbound SMS.
     */
    public function sendSmsV2(string $customerNumber, string $message, string $senderId = null, bool $isMms = false, string $mmsMediaFilePath = null)
    {
        $endpoint = 'v2/sms/send';
        $senderId = $senderId ?? config('zoho.sender_id');

        // Formulate request data format according to API
        $smsData = [
            'customerNumber' => $customerNumber,
            'message' => $message,
            'senderId' => $senderId,
            'mms' => $isMms,
        ];

        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            Log::error('Zoho Voice: SMS failed. Access token not available.');
            return ['status' => 'error', 'message' => 'Token not found'];
        }

        $request = Http::withHeaders([
            'Authorization' => 'Zoho-oauthtoken ' . $accessToken,
            'Accept' => 'application/json',
        ]);

        if ($mmsMediaFilePath && $isMms && file_exists($mmsMediaFilePath)) {
            $request = $request->attach(
                'mms_media',
                file_get_contents($mmsMediaFilePath),
                basename($mmsMediaFilePath)
            )->asMultipart();
        } else {
            $request = $request->asForm();
        }

        try {
            $response = $request->post("{$this->baseUrl}/{$endpoint}", [
                'sms_data' => json_encode($smsData),
            ]);

            Log::info("Zoho Voice SMS response: " . $response->body());
            return $response->json();
        } catch (\Exception $e) {
            Log::error('Zoho Voice SMS error: ' . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Retrieve Call Logs from Zoho.
     */
    public function getCallLogs(array $params = [])
    {
        return $this->makeRequest('get', 'v1/calllogs', $params);
    }

    /**
     * Retrieve SMS Logs.
     */
    public function getSmsLogs(array $params = [])
    {
        return $this->makeRequest('get', 'v1/sms/logs', $params);
    }

    /**
     * Retrieve Users.
     */
    public function getUsers(array $params = [])
    {
        return $this->makeRequest('get', 'v1/users', $params);
    }
}
