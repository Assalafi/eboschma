<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ZohoWebhookController extends Controller
{
    /**
     * Handle incoming webhook requests from Zoho Voice.
     */
    public function handleCallWebhook(Request $request)
    {
        Log::info('Zoho Voice Webhook Triggered:', $request->all());

        $event = $request->input('event');
        $callId = $request->input('call_id');
        $caller = $request->input('caller'); // e.g. caller number
        $receiver = $request->input('receiver'); // e.g. recipient number
        $direction = $request->input('direction', 'inbound'); // inbound / outbound
        $duration = $request->input('duration', 0); // call duration in seconds
        $recordingUrl = $request->input('recording_url');

        // Look up ticket associated with caller or receiver phone numbers
        $phoneNumber = ($direction === 'inbound') ? $caller : $receiver;

        if (empty($phoneNumber)) {
            return response()->json(['success' => false, 'message' => 'Missing phone number']);
        }

        // Clean up phone number format (e.g. remove +234 or leading zeros for query matching)
        $cleanPhone = preg_replace('/^\+234|^234|^\+/', '', $phoneNumber);
        $cleanPhone = ltrim($cleanPhone, '0');

        // Retrieve latest ticket matching cleaner phone number
        $ticket = Ticket::where('phone', 'LIKE', "%{$cleanPhone}%")
            ->orderBy('created_at', 'desc')
            ->first();

        if ($event === 'call.completed') {
            if ($ticket) {
                // Link call to ticket
                DB::table('ticket_calls')->insert([
                    'ticket_id' => $ticket->id,
                    'zoho_call_id' => $callId,
                    'direction' => $direction,
                    'caller' => $caller,
                    'receiver' => $receiver,
                    'duration_seconds' => $duration,
                    'recording_url' => $recordingUrl,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Also insert a ticket reply in the conversation history timeline
                $durationFormatted = gmdate("H:i:s", (int)$duration);
                $message = "📞 **Call Completed via Zoho Voice**\n";
                $message .= "* **Direction**: " . ucfirst($direction) . "\n";
                $message .= "* **Caller**: {$caller}\n";
                $message .= "* **Receiver**: {$receiver}\n";
                $message .= "* **Duration**: {$durationFormatted}\n";
                
                if ($recordingUrl) {
                    $message .= "* **Recording**: [▶ Play / Listen]({$recordingUrl})";
                }

                // Add as an automatic timeline reply (system/internal notes format)
                $ticket->addReply($message, $ticket->assigned_to ?? $ticket->created_by, false);

                Log::info("Zoho Voice: Call successfully linked to Ticket {$ticket->ticket_id}");
            } else {
                Log::warning("Zoho Voice Call Completed: No active ticket found for phone {$phoneNumber}");
            }
        }

        return response()->json(['success' => true]);
    }

    /**
     * Endpoint for live beneficiary search on incoming call lookup.
     */
    public function lookupBeneficiary(Request $request)
    {
        $phone = $request->query('phone');
        if (empty($phone)) {
            return response()->json(['found' => false]);
        }

        $cleanPhone = preg_replace('/^\+234|^234|^\+/', '', $phone);
        $cleanPhone = ltrim($cleanPhone, '0');

        // Check beneficiaries table
        $beneficiary = \App\Models\Beneficiary::where('phone_no', 'LIKE', "%{$cleanPhone}%")->first();
        if ($beneficiary) {
            return response()->json([
                'found' => true,
                'type' => 'beneficiary',
                'name' => $beneficiary->fullname,
                'boschma_no' => $beneficiary->boschma_no,
                'photo' => $beneficiary->photo ? url('storage/' . $beneficiary->photo) : null,
                'status' => $beneficiary->status,
            ]);
        }

        // Check spouses
        $spouse = \App\Models\Spouse::where('phone', 'LIKE', "%{$cleanPhone}%")->first();
        if ($spouse) {
            return response()->json([
                'found' => true,
                'type' => 'spouse',
                'name' => $spouse->name,
                'boschma_no' => $spouse->boschma_no,
                'photo' => $spouse->photo ? url('storage/' . $spouse->photo) : null,
                'status' => $spouse->status ?? 'active',
            ]);
        }

        return response()->json([
            'found' => false,
            'message' => 'Phone number not matched to any registered enrollee.',
        ]);
    }
}
