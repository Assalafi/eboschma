<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ZohoOAuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth'); // Ensure only authenticated users/staff can initiate OAuth flow
    }

    /**
     * Redirect to Zoho Accounts authorization server.
     */
    public function redirectToZoho()
    {
        $clientId = config('zoho.client_id');
        $redirectUri = config('zoho.redirect_uri');
        $accountsUrl = config('zoho.accounts_url');
        $scopes = implode(',', config('zoho.scopes'));

        // Generate authorization URL
        // Using access_type=offline enables returning a refresh_token in the token exchange
        // and prompt=consent ensures the consent screen is shown
        $authUrl = "{$accountsUrl}/oauth/v2/auth?scope={$scopes}&client_id={$clientId}&response_type=code&access_type=offline&prompt=consent&redirect_uri=" . urlencode($redirectUri);

        return redirect($authUrl);
    }

    /**
     * Handle the OAuth redirect callback from Zoho.
     */
    public function handleZohoCallback(Request $request)
    {
        $code = $request->query('code');
        if (!$code) {
            return redirect()->route('crm.index')->with('error', 'Authorization code not received from Zoho.');
        }

        $clientId = config('zoho.client_id');
        $clientSecret = config('zoho.client_secret');
        $redirectUri = config('zoho.redirect_uri');
        $accountsUrl = config('zoho.accounts_url');

        try {
            $response = Http::asForm()->post("{$accountsUrl}/oauth/v2/token", [
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'redirect_uri' => $redirectUri,
                'grant_type' => 'authorization_code',
                'code' => $code,
            ]);

            $data = $response->json();

            if ($response->successful() && isset($data['access_token'])) {
                $expiresIn = $data['expires_in'] ?? 3600;

                // Save or update in DB
                DB::table('zoho_tokens')->updateOrInsert(
                    ['id' => 1],
                    [
                        'access_token' => $data['access_token'],
                        'refresh_token' => $data['refresh_token'] ?? null,
                        'expires_at' => now()->addSeconds($expiresIn),
                        'updated_at' => now(),
                    ]
                );

                return redirect()->route('crm.index')->with('success', 'Zoho Voice API successfully connected and authenticated!');
            } else {
                Log::error('Zoho OAuth Callback failed: ' . json_encode($data));
                return redirect()->route('crm.index')->with('error', 'Failed to exchange Zoho token: ' . ($data['error'] ?? 'Unknown error'));
            }
        } catch (\Exception $e) {
            Log::error('Zoho OAuth Callback error: ' . $e->getMessage());
            return redirect()->route('crm.index')->with('error', 'An error occurred during Zoho Authentication: ' . $e->getMessage());
        }
    }

    /**
     * Check authentication status.
     */
    public function status()
    {
        $token = DB::table('zoho_tokens')->orderBy('id', 'desc')->first();
        if ($token) {
            $expiresAt = \Carbon\Carbon::parse($token->expires_at);
            $hasExpired = $expiresAt->isPast();
            return response()->json([
                'connected' => true,
                'expires_at' => $token->expires_at,
                'has_expired' => $hasExpired,
                'time_left' => $expiresAt->diffForHumans(),
                'has_refresh_token' => !empty($token->refresh_token),
            ]);
        }

        return response()->json([
            'connected' => false,
            'message' => 'No active Zoho Voice connection found.',
        ]);
    }
}
