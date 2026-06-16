<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Ticket;
use App\Services\TwilioService;
use Twilio\TwiML\VoiceResponse;

class TwilioVoiceController extends Controller
{
    protected TwilioService $twilio;

    public function __construct(TwilioService $twilio)
    {
        $this->twilio = $twilio;
    }

    /**
     * Check Twilio connection status.
     * GET /twilio/status
     */
    public function status()
    {
        $valid = $this->twilio->validateCredentials();
        return response()->json([
            'connected' => $valid,
            'from_number' => config('services.twilio.from'),
            'message' => $valid ? 'Twilio credentials are valid and connected.' : 'Failed to validate Twilio credentials.',
        ]);
    }

    /**
     * Generate an Access Token for the Twilio Browser Client
     * GET /twilio/token
     */
    public function generateToken(Request $request)
    {
        // Give each logged-in agent a unique identity so they can call each other.
        // E.g., User ID 1 becomes 'staff_1', User ID 2 becomes 'staff_2'
        $identity = auth()->check() ? 'staff_' . auth()->id() : 'user_' . rand(1000, 9999);
        $token = $this->twilio->generateClientToken($identity);
        
        if (!$token) {
            return response()->json(['error' => 'Unable to generate Twilio Client token.'], 500);
        }

        return response()->json([
            'identity' => $identity,
            'token' => $token
        ]);
    }

    /**
     * TwiML response for outbound calls originated from the Browser Client.
     * POST /twilio/voice/client-outbound
     */
    public function clientOutboundTwiml(Request $request)
    {
        $to = $request->input('To');
        $from = config('services.twilio.from'); // Must be your verified Twilio Number

        $response = new VoiceResponse();
        
        if ($to) {
            $dial = $response->dial('', ['callerId' => $from]);
            
            // Check if the user is dialing another agent (e.g., 'staff_2')
            if (str_starts_with($to, 'staff_') || str_starts_with($to, 'user_')) {
                Log::info("Twilio Client-to-Client call: from={$request->input('From')} to={$to}");
                $dial->client($to);
            } else {
                // Otherwise, they are dialing a real phone number
                $normalizedTo = $this->normalizePhone($to);
                Log::info("Twilio Client outbound call: raw={$to} normalized={$normalizedTo}");
                $dial->number($normalizedTo);
            }
        } else {
            $response->say('Thanks for calling. No destination number was provided.', ['voice' => 'alice']);
        }

        return response($response, 200)->header('Content-Type', 'text/xml');
    }

    /**
     * Normalize any phone number format to E.164 (+countryCode + number).
     * Handles Nigerian numbers: 07xxx, 08xxx, 09xxx → +234xxxxxxxxxx
     * Also handles already-prefixed formats: 234xxx, +234xxx.
     *
     * @param string $phone
     * @param string $countryCode  Default: '234' (Nigeria)
     * @return string
     */
    protected function normalizePhone(string $phone, string $countryCode = '234'): string
    {
        // Strip all non-digit characters except leading +
        $stripped = preg_replace('/[^\d+]/', '', trim($phone));

        // Already in full E.164 format
        if (preg_match('/^\+\d{10,15}$/', $stripped)) {
            return $stripped;
        }

        // Remove leading + if present
        $digits = ltrim($stripped, '+');

        // Already has full country code prefix (e.g. 2349064659803)
        if (str_starts_with($digits, $countryCode) && strlen($digits) >= 12) {
            return '+' . $digits;
        }

        // Local format with leading 0 (e.g. 09064659803 → +2349064659803)
        if (str_starts_with($digits, '0') && strlen($digits) >= 10) {
            return '+' . $countryCode . substr($digits, 1);
        }

        // Local format without leading 0 (e.g. 9064659803 → +2349064659803)
        if (strlen($digits) >= 10) {
            return '+' . $countryCode . $digits;
        }

        // Fallback — return as-is with + prefix
        return '+' . $digits;
    }

    /**
     * Handle inbound TwiML response for incoming calls.
     * POST /twilio/voice/inbound
     */
    public function inboundTwiml(Request $request)
    {
        Log::info('Twilio Inbound Call:', $request->all());

        $caller = $request->input('From', 'Unknown');

        // Lookup the caller in beneficiary tables
        $clean = preg_replace('/^\+234|^234|^\+/', '', $caller);
        $clean = ltrim($clean, '0');

        $beneficiary = \App\Models\Beneficiary::where('phone_no', 'LIKE', "%{$clean}%")->first();
        $name = $beneficiary ? $beneficiary->fullname : 'a caller';

        $response = new VoiceResponse();
        $response->say(
            "Hello {$name}. Please hold while we connect you to an available support agent.",
            ['voice' => 'alice', 'language' => 'en-US']
        );
        
        // Route the call to the WebRTC browser client!
        // We are ringing 'staff_1' (the first registered admin). In a real call center, you would add multiple <Client> tags here or use TaskRouter.
        $dial = $response->dial('', ['timeout' => 30]);
        $dial->client('staff_1');

        return response($response, 200)->header('Content-Type', 'text/xml');
    }

    /**
     * TwiML response for outbound calls (what Twilio says when the recipient picks up).
     * GET /twilio/voice/outbound-twiml
     */
    public function outboundTwiml(Request $request)
    {
        $name = $request->query('name', 'valued customer');

        $response = new VoiceResponse();
        $response->say(
            "Hello {$name}. This is a call from BOSCHMA Support. A support agent will speak with you now.",
            ['voice' => 'alice', 'language' => 'en-US']
        );
        $response->pause(['length' => 2]);
        $response->say("Please hold.", ['voice' => 'alice']);

        return response($response, 200)->header('Content-Type', 'text/xml');
    }

    /**
     * Initiate an outbound call via Twilio REST API.
     * POST /twilio/call
     */
    public function makeCall(Request $request)
    {
        $request->validate([
            'to'       => 'required|string',
            'name'     => 'nullable|string',
            'ticket_id' => 'nullable|exists:tickets,id',
        ]);

        $to = $this->normalizePhone($request->input('to'));
        $name = $request->input('name', 'valued customer');
        $ticketId = $request->input('ticket_id');

        Log::info("Twilio makeCall: raw={$request->input('to')} normalized={$to}");

        // The TwiML URL Twilio will fetch when the call is answered
        $twimlUrl = route('twilio.voice.outbound-twiml') . '?name=' . urlencode($name);

        $result = $this->twilio->makeCall($to, $twimlUrl);

        if ($result['status'] === 'success') {
            // Log call in ticket_calls table if ticket is provided
            if ($ticketId) {
                DB::table('ticket_calls')->insert([
                    'ticket_id'       => $ticketId,
                    'twilio_call_sid' => $result['sid'],
                    'direction'       => 'outbound',
                    'caller'          => config('services.twilio.from'),
                    'receiver'        => $to,
                    'duration_seconds' => 0,
                    'recording_url'   => null,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);

                // Add timeline reply in ticket
                $ticket = Ticket::find($ticketId);
                if ($ticket) {
                    $ticket->addReply(
                        "📞 **Outbound Call Initiated via Twilio**\n" .
                        "* **To**: {$to}\n" .
                        "* **Call SID**: {$result['sid']}\n" .
                        "* **Status**: {$result['call_status']}",
                        auth()->id() ?? $ticket->created_by,
                        false
                    );
                }
            }

            return response()->json([
                'success' => true,
                'sid' => $result['sid'],
                'call_status' => $result['call_status'],
                'message' => 'Call initiated successfully.',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'] ?? 'Failed to initiate call.',
        ], 422);
    }

    /**
     * Handle Twilio Status Callback (call completed, etc.).
     * POST /twilio/webhook/call-status
     */
    public function callStatusCallback(Request $request)
    {
        Log::info('Twilio Call Status Callback:', $request->all());

        $callSid    = $request->input('CallSid');
        $callStatus = $request->input('CallStatus'); // completed, busy, failed, no-answer
        $duration   = (int) $request->input('CallDuration', 0);
        $to         = $request->input('To');
        $from       = $request->input('From');
        $direction  = $request->input('Direction', 'outbound');
        $recordingUrl = $request->input('RecordingUrl');

        // Find matching ticket_call row
        $row = DB::table('ticket_calls')->where('twilio_call_sid', $callSid)->first();
        if ($row) {
            DB::table('ticket_calls')->where('twilio_call_sid', $callSid)->update([
                'duration_seconds' => $duration,
                'recording_url'   => $recordingUrl,
                'updated_at'      => now(),
            ]);

            // Update ticket timeline
            $ticket = Ticket::find($row->ticket_id);
            if ($ticket && $callStatus === 'completed') {
                $durationFormatted = gmdate('H:i:s', $duration);
                $msg  = "📞 **Call Completed via Twilio**\n";
                $msg .= "* **Direction**: " . ucfirst($direction) . "\n";
                $msg .= "* **Caller**: {$from}\n";
                $msg .= "* **Receiver**: {$to}\n";
                $msg .= "* **Duration**: {$durationFormatted}\n";
                if ($recordingUrl) {
                    $msg .= "* **Recording**: [▶ Play / Listen]({$recordingUrl})";
                }
                $ticket->addReply($msg, $ticket->assigned_to ?? $ticket->created_by, false);
            }
        } else {
            // Inbound call — try to match by phone number
            $phone = $from ?? $to;
            $clean = preg_replace('/^\+234|^234|^\+/', '', $phone);
            $clean = ltrim($clean, '0');
            $ticket = Ticket::where('phone', 'LIKE', "%{$clean}%")
                ->orderBy('created_at', 'desc')
                ->first();

            if ($ticket) {
                DB::table('ticket_calls')->insert([
                    'ticket_id'        => $ticket->id,
                    'twilio_call_sid'  => $callSid,
                    'direction'        => $direction,
                    'caller'           => $from,
                    'receiver'         => $to,
                    'duration_seconds' => $duration,
                    'recording_url'    => $recordingUrl,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);
            }
        }

        return response('', 204);
    }

    /**
     * Lookup a beneficiary by incoming phone number.
     * GET /twilio/lookup
     */
    public function lookupBeneficiary(Request $request)
    {
        $phone = $request->query('phone');
        if (empty($phone)) {
            return response()->json(['found' => false]);
        }

        $clean = preg_replace('/^\+234|^234|^\+/', '', $phone);
        $clean = ltrim($clean, '0');

        $beneficiary = \App\Models\Beneficiary::where('phone_no', 'LIKE', "%{$clean}%")->first();
        if ($beneficiary) {
            return response()->json([
                'found'      => true,
                'type'       => 'beneficiary',
                'name'       => $beneficiary->fullname,
                'boschma_no' => $beneficiary->boschma_no,
                'photo'      => $beneficiary->photo ? url('storage/' . $beneficiary->photo) : null,
                'status'     => $beneficiary->status,
            ]);
        }

        $spouse = \App\Models\Spouse::where('phone', 'LIKE', "%{$clean}%")->first();
        if ($spouse) {
            return response()->json([
                'found'      => true,
                'type'       => 'spouse',
                'name'       => $spouse->name,
                'boschma_no' => $spouse->boschma_no,
                'photo'      => $spouse->photo ? url('storage/' . $spouse->photo) : null,
                'status'     => $spouse->status ?? 'active',
            ]);
        }

        return response()->json([
            'found'   => false,
            'message' => 'Phone number not matched to any registered enrollee.',
        ]);
    }
}
