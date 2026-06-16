<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\TicketCategory;
use App\Models\Facility;
use App\Models\Staff;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CrmController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of tickets.
     */
    public function index(Request $request)
    {
        // Get latest reply times for all tickets
        $latestReplies = \DB::table('ticket_replies')
            ->select('ticket_id', \DB::raw('MAX(created_at) as latest_reply_at'))
            ->groupBy('ticket_id')
            ->pluck('latest_reply_at', 'ticket_id');

        $query = Ticket::with(['category', 'facility', 'assignedUser', 'createdBy'])
            ->select('*')
            ->selectRaw('CASE 
                WHEN (SELECT MAX(created_at) FROM ticket_replies WHERE ticket_id = tickets.id) > tickets.created_at 
                THEN (SELECT MAX(created_at) FROM ticket_replies WHERE ticket_id = tickets.id)
                ELSE tickets.created_at 
            END as latest_activity')
            ->orderBy('latest_activity', 'desc');

        // Filter by status
        if ($request->has('status') && $request->status != '') {
            $query->status($request->status);
        }

        // Filter by priority
        if ($request->has('priority') && $request->priority != '') {
            $query->priority($request->priority);
        }

        // Filter by assigned user
        if ($request->has('assigned_to') && $request->assigned_to != '') {
            $query->assignedTo($request->assigned_to);
        }

        // Filter by category
        if ($request->has('category') && $request->category != '') {
            $query->where('ticket_category_id', $request->category);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('ticket_id', 'LIKE', "%{$search}%")
                  ->orWhere('name', 'LIKE', "%{$search}%")
                  ->orWhere('boschma_no', 'LIKE', "%{$search}%")
                  ->orWhere('complaint', 'LIKE', "%{$search}%");
            });
        }

        $ccFacilityIds = $this->getCustomerCareFacilityIds();
        if ($ccFacilityIds !== null) {
            $query->whereIn('facility_id', $ccFacilityIds);
        }

        $tickets = $query->paginate(15);
        $categories = TicketCategory::active()->get();
        $staff = Staff::orderBy('fullname')->get();

        return view('admin.crm.index', compact('tickets', 'categories', 'staff'));
    }

    /**
     * Show the form for creating a new ticket.
     */
    public function create()
    {
        $categories = TicketCategory::active()->get();
        $facilities = Facility::orderBy('name')->get();
        $staff = Staff::orderBy('fullname')->get();
        
        return view('admin.crm.create', compact('categories', 'facilities', 'staff'));
    }

    /**
     * Store a newly created ticket.
     */
    public function store(Request $request)
    {
        // Debug logging
        \Log::info('CRM Store method called');
        \Log::info('Request data: ' . json_encode($request->all()));
        
        $isOutsider = $request->boolean('is_outsider', false);
        
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'phone' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255',
                'boschma_no' => 'nullable|string|max:50', // Temporarily always nullable
                'facility_id' => 'nullable|exists:facilities,id',
                'ticket_category_id' => 'required|exists:ticket_categories,id',
                'assigned_to' => 'nullable|exists:staff,id',
                'complaint' => 'required|string',
                'description' => 'nullable|string',
                'department' => 'nullable|string|max:255',
                'sla_hours' => 'required|integer|min:1|max:168',
                'priority' => 'required|in:low,medium,high,urgent',
                'attachments.*' => 'nullable|file|max:10240',
                'is_outsider' => 'sometimes|boolean' // Changed from required boolean to sometimes boolean
            ]);
            
            \Log::info('Validation passed');
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed: ' . json_encode($e->errors()));
            // Return with errors instead of throwing to see what happens
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        }

        $data = $request->except('attachments');
        $data['created_by'] = Auth::id();

        // Generate sequential ticket ID
        $data['ticket_id'] = $this->generateSequentialTicketId();

        // Validate and fetch Boschma No information (only for non-outsiders)
        if (!$isOutsider && !empty($data['boschma_no'])) {
            $beneficiaryInfo = $this->getBeneficiaryInfo($data['boschma_no']);
            if ($beneficiaryInfo) {
                // Auto-populate name and other info from beneficiary record
                $data['name'] = $beneficiaryInfo['name'];
                
                // Only populate if beneficiary info has the data and it's not already set
                if (isset($beneficiaryInfo['phone']) && $beneficiaryInfo['phone'] !== null) {
                    $data['phone'] = $beneficiaryInfo['phone'];
                }
                
                if (isset($beneficiaryInfo['email']) && $beneficiaryInfo['email'] !== null) {
                    $data['email'] = $beneficiaryInfo['email'];
                }
                
                if (isset($beneficiaryInfo['facility_id']) && $beneficiaryInfo['facility_id'] !== null) {
                    $data['facility_id'] = $beneficiaryInfo['facility_id'];
                }
                
                $data['beneficiary_type'] = $beneficiaryInfo['type']; // beneficiary, spouse, or child
            } else {
                // Mark as non-beneficiary
                $data['beneficiary_type'] = 'non_beneficiary';
            }
        } elseif ($isOutsider) {
            // For outsiders, set beneficiary type to outsider
            $data['beneficiary_type'] = 'outsider';
        } else {
            // Empty Boschma No but not outsider
            $data['beneficiary_type'] = 'non_beneficiary';
        }

        // Handle multiple attachments
        $attachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                if ($file && $file->isValid()) {
                    $path = $file->store('ticket_attachments', 'public');
                    $attachments[] = [
                        'path' => $path,
                        'name' => $file->getClientOriginalName(),
                        'size' => $file->getSize(),
                        'mime_type' => $file->getMimeType()
                    ];
                }
            }
        }
        
        // Store attachments as JSON
        if (!empty($attachments)) {
            $data['attachments'] = json_encode($attachments);
        }

        $ticket = Ticket::create($data);

        // Create notification for assigned user when ticket is created
        if (!empty($data['assigned_to']) && $data['assigned_to'] != Auth::id()) {
            \App\Models\Notification::create([
                'user_id' => $data['assigned_to'],
                'ticket_id' => $ticket->id,
                'type' => 'assigned',
                'title' => 'New Ticket Assigned',
                'message' => Auth::user()->fullname . ' assigned you a new ticket: ' . $ticket->ticket_id,
            ]);
        }

        // Add initial reply if there's an attachment
        if ($request->hasFile('attachment')) {
            $ticket->addReply(
                'Initial attachment uploaded',
                Auth::id(),
                false,
                [
                    'path' => $path,
                    'name' => $file->getClientOriginalName()
                ]
            );
        }

        // Automated SMS notification via Twilio
        if (!empty($ticket->phone)) {
            try {
                $smsService = resolve(\App\Services\TwilioService::class);
                $smsMessage = "BOSCHMA Support: Hello {$ticket->name}, your ticket {$ticket->ticket_id} has been successfully raised. An agent is reviewing it. Thank you.";
                $smsService->sendSmsV2($ticket->phone, $smsMessage);
            } catch (\Exception $e) {
                \Log::error("Twilio SMS creation alert failed: " . $e->getMessage());
            }
        }

        return redirect()->route('crm.show', $ticket->id)
            ->with('success', 'Ticket created successfully!');
    }

    /**
     * Display the specified ticket.
     */
    public function show($id)
    {
        $ticket = Ticket::with([
            'category', 
            'facility', 
            'assignedUser', 
            'createdBy',
            'replies.user'
        ])->findOrFail($id);

        $currentStaff = Auth::user();
        
        // Fetch beneficiary information if Boschma No exists
        $beneficiaryInfo = null;
        if ($ticket->boschma_no) {
            $beneficiaryInfo = $this->getBeneficiaryInfo($ticket->boschma_no);
        }
        
        // Mark replies as read for both assigned user and ticket creator
        // This ensures bidirectional read tracking
        if ($ticket->assigned_to === $currentStaff->id || $ticket->created_by === $currentStaff->id) {
            $unreadReplies = $ticket->replies()
                ->where('user_id', '!=', $currentStaff->id) // Don't mark own replies
                ->whereNull('read_by_assigned_at')
                ->get();
                
            foreach ($unreadReplies as $reply) {
                $reply->read_by_assigned_at = now();
                $reply->save();
            }
        }

        return view('admin.crm.show', compact('ticket', 'beneficiaryInfo'));
    }

    /**
     * Show the form for editing the specified ticket.
     */
    public function edit($id)
    {
        $ticket = Ticket::findOrFail($id);
        $currentStaff = Auth::user();

        // Check if current staff can edit this ticket (only ticket riser)
        if (!$ticket->canBeEditedBy($currentStaff)) {
            return redirect()->route('crm.show', $ticket->id)
                ->with('error', 'Only the ticket creator can edit this ticket.');
        }

        $categories = TicketCategory::active()->get();
        $facilities = Facility::orderBy('name')->get();
        $staff = Staff::orderBy('fullname')->get();

        return view('admin.crm.edit', compact('ticket', 'categories', 'facilities', 'staff'));
    }

    /**
     * Update the specified ticket.
     */
    public function update(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);
        $currentStaff = Auth::user();

        // Check if current staff can edit this ticket (only ticket riser)
        if (!$ticket->canBeEditedBy($currentStaff)) {
            return redirect()->route('crm.show', $ticket->id)
                ->with('error', 'Only the ticket creator can edit this ticket.');
        }

        // Prevent editing if status is completed
        if ($ticket->status === 'completed') {
            return redirect()->route('crm.show', $ticket->id)
                ->with('error', 'Completed tickets cannot be edited.');
        }

        $isOutsider = $request->boolean('is_outsider', false);

        // Different validation rules based on ticket status
        if ($ticket->status === 'in_progress') {
            // Allow updating assigned_to, sla_hours, priority, and status for in_progress tickets
            $request->validate([
                'assigned_to' => 'required|exists:staff,id',
                'sla_hours' => 'required|integer|min:1|max:168',
                'priority' => 'required|in:low,medium,high,urgent',
                'status' => 'required|in:in_progress,completed'
            ]);
        } else {
            // Full validation for pending tickets
            $request->validate([
                'name' => 'required|string|max:255',
                'phone' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255',
                'boschma_no' => $isOutsider ? 'nullable|string|max:50' : 'required|string|max:50',
                'facility_id' => 'nullable|exists:facilities,id',
                'ticket_category_id' => 'required|exists:ticket_categories,id',
                'assigned_to' => 'nullable|exists:staff,id',
                'complaint' => 'required|string',
                'description' => 'nullable|string',
                'department' => 'nullable|string|max:255',
                'sla_hours' => 'required|integer|min:1|max:168',
                'priority' => 'required|in:low,medium,high,urgent',
                'status' => 'required|in:in_progress,completed', // Only allow in_progress or completed
                'is_outsider' => 'sometimes|boolean'
            ]);
        }

        $data = $request->all();

        // Update beneficiary type based on outsider status and Boschma No (only for pending tickets)
        if ($ticket->status === 'pending') {
            if ($isOutsider) {
                $data['beneficiary_type'] = 'outsider';
            } elseif (!empty($data['boschma_no'])) {
                $beneficiaryInfo = $this->getBeneficiaryInfo($data['boschma_no']);
                if ($beneficiaryInfo) {
                    $data['beneficiary_type'] = $beneficiaryInfo['type'];
                    // Auto-populate other info if available
                    if (isset($beneficiaryInfo['phone']) && $beneficiaryInfo['phone'] !== null) {
                        $data['phone'] = $beneficiaryInfo['phone'];
                    }
                    if (isset($beneficiaryInfo['email']) && $beneficiaryInfo['email'] !== null) {
                        $data['email'] = $beneficiaryInfo['email'];
                    }
                    if (isset($beneficiaryInfo['facility_id']) && $beneficiaryInfo['facility_id'] !== null) {
                        $data['facility_id'] = $beneficiaryInfo['facility_id'];
                    }
                } else {
                    $data['beneficiary_type'] = 'non_beneficiary';
                }
            } else {
                $data['beneficiary_type'] = 'non_beneficiary';
            }
        }

        $ticket->update($data);

        return redirect()->route('crm.show', $ticket->id)
            ->with('success', 'Ticket updated successfully!');
    }

    /**
     * Mark ticket as completed
     */
    public function markCompleted($id)
    {
        $ticket = Ticket::findOrFail($id);
        $currentStaff = Auth::user();

        // Check if current staff can complete this ticket (only ticket riser)
        if (!$ticket->canBeCompletedBy($currentStaff)) {
            return redirect()->route('crm.show', $ticket->id)
                ->with('error', 'Only the ticket creator can mark it as completed.');
        }

        $ticket->markAsCompleted();

        // Automated SMS notification when ticket is resolved
        if (!empty($ticket->phone)) {
            try {
                $smsService = resolve(\App\Services\TwilioService::class);
                $smsMessage = "BOSCHMA Support: Hello {$ticket->name}, your ticket {$ticket->ticket_id} has been marked as completed/resolved. Thank you.";
                $smsService->sendSmsV2($ticket->phone, $smsMessage);
            } catch (\Exception $e) {
                \Log::error("Twilio SMS completion alert failed: " . $e->getMessage());
            }
        }

        return redirect()->route('crm.show', $ticket->id)
            ->with('success', 'Ticket marked as completed successfully!');
    }

    /**
     * Remove the specified ticket.
     */
    public function destroy($id)
    {
        $ticket = Ticket::findOrFail($id);
        $currentStaff = Auth::user();

        // Check if current staff can delete this ticket
        if (!$ticket->canBeDeletedBy($currentStaff)) {
            $errorMessage = 'You cannot delete this ticket.';
            if ($ticket->created_by !== $currentStaff->id) {
                $errorMessage = 'Only the ticket creator can delete this ticket.';
            } elseif ($ticket->status !== 'pending') {
                $errorMessage = 'Only pending tickets can be deleted.';
            }
            
            return redirect()->route('crm.show', $ticket->id)
                ->with('error', $errorMessage);
        }

        $ticket->delete();

        return redirect()->route('crm.index')
            ->with('success', 'Ticket deleted successfully!');
    }

    /**
     * Add reply to ticket
     */
    public function addReply(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string',
            'attachments.*' => 'nullable|file|max:10240'
        ]);

        $ticket = Ticket::findOrFail($id);
        $currentStaff = Auth::user();

        // Check if current staff can reply to this ticket
        if (!$ticket->canBeRepliedBy($currentStaff)) {
            return redirect()->route('crm.show', $ticket->id)
                ->with('error', 'Only the ticket creator or assigned user can reply to this ticket.');
        }

        $attachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                if ($file && $file->isValid()) {
                    $path = $file->store('ticket_attachments', 'public');
                    $attachments[] = [
                        'path' => $path,
                        'name' => $file->getClientOriginalName(),
                        'size' => $file->getSize(),
                        'mime_type' => $file->getMimeType()
                    ];
                }
            }
        }

        $ticket->addReply(
            $request->message,
            Auth::id(),
            false, // Always public reply since internal note checkbox was removed
            $attachments
        );

        // Create notification for the other user
        $otherUserId = ($ticket->created_by === Auth::id()) ? $ticket->assigned_to : $ticket->created_by;
        
        if ($otherUserId) {
            \App\Models\Notification::create([
                'user_id' => $otherUserId,
                'ticket_id' => $ticket->id,
                'type' => 'reply',
                'title' => 'New Reply',
                'message' => Auth::user()->fullname . ' replied to ticket ' . $ticket->ticket_id,
            ]);
        }

        // Automated SMS notification to client if replied by agent
        if (Auth::id() !== $ticket->created_by && !empty($ticket->phone)) {
            try {
                $smsService = resolve(\App\Services\TwilioService::class);
                $smsMessage = "BOSCHMA Support: Hello {$ticket->name}, support agent " . Auth::user()->fullname . " has updated your ticket {$ticket->ticket_id}.";
                $smsService->sendSmsV2($ticket->phone, $smsMessage);
            } catch (\Exception $e) {
                \Log::error("Twilio SMS reply alert failed: " . $e->getMessage());
            }
        }

        return redirect()->route('crm.show', $ticket->id)
            ->with('success', 'Reply added successfully!');
    }

    /**
     * Mark ticket as viewed (update session)
     */
    public function markViewed($id)
    {
        $ticket = Ticket::findOrFail($id);
        
        // Store last viewed timestamp in session
        session(['ticket_viewed_' . $ticket->id => now()]);
        
        return response()->json(['success' => true]);
    }

    /**
     * Mark reply as read by assigned user or ticket creator
     */
    public function markReplyAsRead($replyId)
    {
        $reply = TicketReply::findOrFail($replyId);
        $currentStaff = Auth::user();
        
        // Only assigned user or ticket creator can mark replies as read
        if ($reply->ticket->assigned_to !== $currentStaff->id && $reply->ticket->created_by !== $currentStaff->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        
        // Don't mark own replies as read
        if ($reply->user_id === $currentStaff->id) {
            return response()->json(['success' => true]);
        }
        
        // Update read timestamp
        $reply->read_by_assigned_at = now();
        $reply->save();
        
        return response()->json(['success' => true]);
    }

    /**
     * Get notifications for current user
     */
    public function getNotifications()
    {
        $notifications = \App\Models\Notification::where('user_id', Auth::id())
            ->with('ticket')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'read' => $notification->read,
                    'time' => $notification->created_at->diffForHumans(),
                    'ticket_id' => $notification->ticket_id,
                    'ticket_number' => $notification->ticket ? $notification->ticket->ticket_id : null,
                ];
            });

        return response()->json([
            'success' => true,
            'notifications' => $notifications
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markNotificationAsRead($id)
    {
        $notification = \App\Models\Notification::findOrFail($id);
        
        if ($notification->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    /**
     * Mark all notifications as read for current user
     */
    public function markAllNotificationsAsRead(Request $request)
    {
        $query = \App\Models\Notification::where('user_id', Auth::id())
            ->where('read', false);

        // If ticket_id is provided, only mark notifications for that ticket
        if ($request->has('ticket_id')) {
            $query->where('ticket_id', $request->ticket_id);
        }

        $query->update([
            'read' => true,
            'read_at' => now()
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * View all notifications page
     */
    public function viewAllNotifications()
    {
        $notifications = \App\Models\Notification::where('user_id', Auth::id())
            ->with('ticket')
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return view('admin.crm.notifications', compact('notifications'));
    }

    /**
     * Update ticket status
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,in_progress,completed'
        ]);

        $ticket = Ticket::findOrFail($id);
        $ticket->status = $request->status;
        $ticket->save();

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully!',
            'status' => $ticket->status,
            'status_color' => $ticket->getStatusColor()
        ]);
    }

    /**
     * Get ticket statistics
     */
    public function stats()
    {
        $ccFacilityIds = $this->getCustomerCareFacilityIds();
        
        $baseQuery = \App\Models\Ticket::query();
        if ($ccFacilityIds !== null) {
            $baseQuery->whereIn('facility_id', $ccFacilityIds);
        }
        
        $stats = [
            'total' => (clone $baseQuery)->count(),
            'pending' => (clone $baseQuery)->status('pending')->count(),
            'in_progress' => (clone $baseQuery)->status('in_progress')->count(),
            'completed' => (clone $baseQuery)->status('completed')->count(),
            'overdue' => (clone $baseQuery)->overdue()->count(),
            'my_tickets' => (clone $baseQuery)->assignedTo(Auth::guard('staff')->id() ?? Auth::id())->count()
        ];

        return response()->json($stats);
    }

    /**
     * Generate sequential ticket ID
     */
    private function generateSequentialTicketId()
    {
        // Use database transaction to prevent race conditions
        return DB::transaction(function () {
            // Get the current maximum ticket number from existing tickets
            $maxTicket = Ticket::where('ticket_id', 'like', 'TKT-%')
                ->orderByRaw('CAST(SUBSTRING(ticket_id, 5) AS UNSIGNED) DESC')
                ->lockForUpdate() // Prevent concurrent reads
                ->first();
            
            if ($maxTicket && preg_match('/TKT-(\d+)/', $maxTicket->ticket_id, $matches)) {
                $lastNumber = (int) $matches[1];
                $newNumber = $lastNumber + 1;
            } else {
                $newNumber = 1;
            }
            
            $newTicketId = 'TKT-' . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
            
            // Double-check that this ID doesn't exist (extra safety)
            while (Ticket::where('ticket_id', $newTicketId)->exists()) {
                $newNumber++;
                $newTicketId = 'TKT-' . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
            }
            
            return $newTicketId;
        });
    }

    /**
     * Get beneficiary information from Boschma No
     */
    private function getBeneficiaryInfo($boschmaNo)
    {
        // Check in beneficiaries table first
        $beneficiary = \App\Models\Beneficiary::where('boschma_no', $boschmaNo)->first();
        if ($beneficiary) {
            return [
                'name' => $beneficiary->fullname,
                'phone' => $beneficiary->phone_no,
                'email' => $beneficiary->email,
                'facility_id' => $beneficiary->facility_id,
                'photo' => $beneficiary->photo ? url('storage/' . $beneficiary->photo) : null,
                'gender' => $beneficiary->gender,
                'nin' => $beneficiary->nin,
                'date_of_birth' => $beneficiary->date_of_birth,
                'status' => $beneficiary->status,
                'type' => 'beneficiary'
            ];
        }

        // Check in spouses table
        $spouse = \App\Models\Spouse::where('boschma_no', $boschmaNo)->first();
        if ($spouse) {
            return [
                'name' => $spouse->name,
                'phone' => $spouse->phone,
                'email' => $spouse->email,
                'facility_id' => $spouse->facility_id,
                'photo' => $spouse->photo ? url('storage/' . $spouse->photo) : null,
                'gender' => $spouse->gender,
                'nin' => $spouse->nin,
                'date_of_birth' => $spouse->dob,
                'status' => $spouse->status ?? 'active',
                'type' => 'spouse'
            ];
        }

        // Check in children table
        $child = \App\Models\Child::where('boschma_no', $boschmaNo)->first();
        if ($child) {
            return [
                'name' => $child->name,
                'phone' => null, // Children don't have phone numbers
                'email' => null, // Children don't have email
                'facility_id' => $child->facility_id,
                'photo' => $child->photo ? url('storage/' . $child->photo) : null,
                'gender' => $child->gender,
                'nin' => $child->nin,
                'date_of_birth' => $child->dob,
                'status' => $child->status ?? 'active',
                'type' => 'child'
            ];
        }

        return null; // Not found in any table
    }

    /**
     * Validate Boschma No and return beneficiary info
     */
    public function validateBoschmaNo($boschmaNo)
    {
        $beneficiaryInfo = $this->getBeneficiaryInfo($boschmaNo);
        
        if ($beneficiaryInfo) {
            return response()->json([
                'found' => true,
                'name' => $beneficiaryInfo['name'],
                'phone' => $beneficiaryInfo['phone'],
                'email' => $beneficiaryInfo['email'],
                'facility_id' => $beneficiaryInfo['facility_id'],
                'photo' => $beneficiaryInfo['photo'],
                'gender' => $beneficiaryInfo['gender'],
                'nin' => $beneficiaryInfo['nin'],
                'date_of_birth' => $beneficiaryInfo['date_of_birth'],
                'status' => $beneficiaryInfo['status'],
                'type' => ucfirst($beneficiaryInfo['type'])
            ]);
        } else {
            return response()->json([
                'found' => false,
                'message' => 'Boschma No not found in beneficiaries, spouses, or children records'
            ]);
        }
    }

    /**
     * Download attachment
     */
    public function downloadAttachment($replyId)
    {
        $reply = TicketReply::findOrFail($replyId);
        
        if (!$reply->attachment_path) {
            abort(404);
        }

        $path = storage_path('app/public/' . $reply->attachment_path);
        
        if (!file_exists($path)) {
            abort(404);
        }

        return response()->download($path, $reply->attachment_name);
    }

    /**
     * Send custom SMS to enrollee via Twilio.
     */
    public function sendCustomSms(Request $request)
    {
        $request->validate([
            'phone' => 'required',
            'message' => 'required|string',
            'ticket_id' => 'required|exists:tickets,id',
        ]);

        try {
            $smsService = resolve(\App\Services\TwilioService::class);
            $response = $smsService->sendSmsV2($request->phone, $request->message);

            // Log SMS dispatch in ticket timeline
            $ticket = Ticket::findOrFail($request->ticket_id);
            $ticket->addReply(
                "💬 **SMS Sent via Twilio**\n" .
                "* **Recipient**: {$request->phone}\n" .
                "* **Message**: {$request->message}",
                Auth::id(),
                false
            );

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Search for beneficiaries by NIN, BOSCHMA ID, Name, or Phone Number
     */
    public function searchBeneficiary(Request $request)
    {
        $query = $request->get('q', '');
        
        if (empty($query)) {
            return response()->json([
                'success' => false,
                'message' => 'Please provide a search query'
            ]);
        }

        // Build search results from beneficiaries table
        $beneficiaries = \App\Models\Beneficiary::with('facility')
            ->where(function($q) use ($query) {
                $q->where('boschma_no', 'LIKE', "%{$query}%")
                  ->orWhere('nin', 'LIKE', "%{$query}%")
                  ->orWhere('fullname', 'LIKE', "%{$query}%")
                  ->orWhere('phone_no', 'LIKE', "%{$query}%");
            })
            ->where('status', '!=', 'draft')
            ->limit(10)
            ->get()
            ->map(function($beneficiary) {
                return [
                    'id' => $beneficiary->id,
                    'boschma_id' => $beneficiary->boschma_no,
                    'name' => $beneficiary->fullname,
                    'facility' => $beneficiary->facility ? $beneficiary->facility->name : 'N/A',
                    'facility_id' => $beneficiary->facility_id,
                    'gender' => $beneficiary->gender,
                    'phone' => $beneficiary->phone_no,
                    'nin' => $beneficiary->nin,
                    'status' => $beneficiary->status,
                    'type' => 'beneficiary',
                    'last_updated' => $beneficiary->updated_at ? $beneficiary->updated_at->format('Y-m-d H:i') : 'N/A',
                    'email' => $beneficiary->email,
                    'date_of_birth' => $beneficiary->date_of_birth,
                    'photo' => $beneficiary->photo ? url('storage/' . $beneficiary->photo) : null
                ];
            });

        // Build search results from spouses table
        $spouses = \App\Models\Spouse::with('beneficiary.facility')
            ->where(function($q) use ($query) {
                $q->where('boschma_no', 'LIKE', "%{$query}%")
                  ->orWhere('nin', 'LIKE', "%{$query}%")
                  ->orWhere('name', 'LIKE', "%{$query}%")
                  ->orWhere('phone', 'LIKE', "%{$query}%");
            })
            ->limit(10)
            ->get()
            ->map(function($spouse) {
                return [
                    'id' => $spouse->id,
                    'boschma_id' => $spouse->boschma_no,
                    'name' => $spouse->name,
                    'facility' => $spouse->beneficiary && $spouse->beneficiary->facility ? $spouse->beneficiary->facility->name : 'N/A',
                    'facility_id' => $spouse->facility_id,
                    'gender' => $spouse->gender,
                    'phone' => $spouse->phone,
                    'nin' => $spouse->nin,
                    'status' => $spouse->status ?? 'active',
                    'type' => 'spouse',
                    'last_updated' => $spouse->updated_at ? $spouse->updated_at->format('Y-m-d H:i') : 'N/A',
                    'email' => $spouse->email,
                    'date_of_birth' => $spouse->dob,
                    'photo' => $spouse->photo ? url('storage/' . $spouse->photo) : null
                ];
            });

        // Build search results from children table
        $children = \App\Models\Child::with('beneficiary.facility')
            ->where(function($q) use ($query) {
                $q->where('boschma_no', 'LIKE', "%{$query}%")
                  ->orWhere('nin', 'LIKE', "%{$query}%")
                  ->orWhere('name', 'LIKE', "%{$query}%");
            })
            ->limit(10)
            ->get()
            ->map(function($child) {
                return [
                    'id' => $child->id,
                    'boschma_id' => $child->boschma_no,
                    'name' => $child->name,
                    'facility' => $child->beneficiary && $child->beneficiary->facility ? $child->beneficiary->facility->name : 'N/A',
                    'facility_id' => $child->facility_id,
                    'gender' => $child->gender,
                    'phone' => null,
                    'nin' => $child->nin,
                    'status' => $child->status ?? 'active',
                    'type' => 'child',
                    'last_updated' => $child->updated_at ? $child->updated_at->format('Y-m-d H:i') : 'N/A',
                    'email' => null,
                    'date_of_birth' => $child->dob,
                    'photo' => $child->photo ? url('storage/' . $child->photo) : null
                ];
            });

        // Merge all results
        $results = $beneficiaries->concat($spouses)->concat($children)
            ->unique(function($item) {
                return $item['boschma_id'] . $item['name'];
            })
            ->values()
            ->take(10);

        if ($results->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No beneficiaries found matching your search',
                'results' => []
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Beneficiaries found',
            'results' => $results
        ]);
    }

    /**
     * Get beneficiary profile with visit history
     */
    public function getBeneficiaryProfile(Request $request, $id)
    {
        try {
            // Find beneficiary by ID or boschma_no
            $beneficiary = \App\Models\Beneficiary::where('id', $id)
                ->orWhere('boschma_no', $id)
                ->with('facility')
                ->first();

            if (!$beneficiary) {
                // Try to find in spouses table
                $spouse = \App\Models\Spouse::where('id', $id)
                    ->orWhere('boschma_no', $id)
                    ->with(['beneficiary', 'beneficiary.facility'])
                    ->first();

                if ($spouse) {
                    $beneficiaryInfo = [
                        'id' => $spouse->id,
                        'boschma_id' => $spouse->boschma_no,
                        'name' => $spouse->name,
                        'facility' => $spouse->beneficiary && $spouse->beneficiary->facility ? $spouse->beneficiary->facility->name : 'N/A',
                        'facility_id' => $spouse->facility_id,
                        'gender' => $spouse->gender,
                        'phone' => $spouse->phone,
                        'status' => $spouse->status ?? 'active',
                        'type' => 'spouse',
                        'photo' => $spouse->photo ? url('storage/' . $spouse->photo) : null,
                        'date_of_birth' => $spouse->dob
                    ];
                } else {
                    // Try to find in children table
                    $child = \App\Models\Child::where('id', $id)
                        ->orWhere('boschma_no', $id)
                        ->with(['beneficiary', 'beneficiary.facility'])
                        ->first();

                    if ($child) {
                        $beneficiaryInfo = [
                            'id' => $child->id,
                            'boschma_id' => $child->boschma_no,
                            'name' => $child->name,
                            'facility' => $child->beneficiary && $child->beneficiary->facility ? $child->beneficiary->facility->name : 'N/A',
                            'facility_id' => $child->facility_id,
                            'gender' => $child->gender,
                            'phone' => null,
                            'status' => $child->status ?? 'active',
                            'type' => 'child',
                            'photo' => $child->photo ? url('storage/' . $child->photo) : null,
                            'date_of_birth' => $child->dob
                        ];
                    } else {
                        return response()->json([
                            'success' => false,
                            'message' => 'Beneficiary not found'
                        ], 404);
                    }
                }
            } else {
                $beneficiaryInfo = [
                    'id' => $beneficiary->id,
                    'boschma_id' => $beneficiary->boschma_no,
                    'name' => $beneficiary->fullname,
                    'facility' => $beneficiary->facility ? $beneficiary->facility->name : 'N/A',
                    'facility_id' => $beneficiary->facility_id,
                    'gender' => $beneficiary->gender,
                    'phone' => $beneficiary->phone_no,
                    'status' => $beneficiary->status,
                    'type' => 'beneficiary',
                    'photo' => $beneficiary->photo ? url('storage/' . $beneficiary->photo) : null,
                    'date_of_birth' => $beneficiary->date_of_birth
                ];
            }

            // Get patient record to fetch encounters
            $patientBoschmaNo = $beneficiaryInfo['boschma_id'];
            $patient = \App\Models\Patient::where('enrollee_number', $patientBoschmaNo)->first();

            $lastVisitDate = 'N/A';
            $currentStatus = $beneficiaryInfo['status'];

            if ($patient) {
                // Get the most recent encounter to determine current status
                $latestEncounter = \App\Models\Encounter::where('patient_id', $patient->id)
                    ->latest('visit_date')
                    ->first();

                if ($latestEncounter) {
                    $lastVisitDate = $latestEncounter->visit_date->format('Y-m-d H:i');
                    // Map encounter status to patient status
                    if ($latestEncounter->status === 'In Progress') {
                        $currentStatus = 'In Consultation';
                    } elseif ($latestEncounter->status === 'Completed') {
                        $currentStatus = 'Completed';
                    } else {
                        $currentStatus = 'Waiting';
                    }
                }

                // Get paginated visit history
                $page = $request->get('page', 1);
                $perPage = $request->get('per_page', 10);

                $encounters = \App\Models\Encounter::where('patient_id', $patient->id)
                    ->with(['facility', 'program'])
                    ->orderBy('visit_date', 'desc')
                    ->paginate($perPage, ['*'], 'page', $page);

                $visitHistory = $encounters->map(function($encounter) {
                    return [
                        'id' => $encounter->id,
                        'visit_date' => $encounter->visit_date->format('Y-m-d H:i'),
                        'nature_of_visit' => $encounter->nature_of_visit,
                        'reason_for_visit' => $encounter->reason_for_visit,
                        'status' => $encounter->status,
                        'facility' => $encounter->facility ? $encounter->facility->name : 'N/A',
                        'mode_of_entry' => $encounter->mode_of_entry
                    ];
                });

                $visitHistoryPagination = [
                    'total' => $encounters->total(),
                    'per_page' => $encounters->perPage(),
                    'current_page' => $encounters->currentPage(),
                    'last_page' => $encounters->lastPage(),
                    'from' => $encounters->firstItem(),
                    'to' => $encounters->lastItem(),
                    'data' => $visitHistory
                ];
            } else {
                $visitHistoryPagination = [
                    'total' => 0,
                    'per_page' => 10,
                    'current_page' => 1,
                    'last_page' => 0,
                    'from' => null,
                    'to' => null,
                    'data' => []
                ];
            }

            return response()->json([
                'success' => true,
                'profile' => [
                    'id' => $beneficiaryInfo['id'],
                    'boschma_id' => $beneficiaryInfo['boschma_id'],
                    'name' => $beneficiaryInfo['name'],
                    'enrolled_facility' => $beneficiaryInfo['facility'],
                    'current_status' => $currentStatus,
                    'last_visit_date' => $lastVisitDate,
                    'gender' => $beneficiaryInfo['gender'],
                    'phone' => $beneficiaryInfo['phone'],
                    'type' => $beneficiaryInfo['type'],
                    'photo' => $beneficiaryInfo['photo'],
                    'date_of_birth' => $beneficiaryInfo['date_of_birth']
                ],
                'visit_history' => $visitHistoryPagination
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching beneficiary profile: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get Facility Activity Overview
     */
    public function facilityActivity(Request $request)
    {
        $facilityIds = $this->getCustomerCareFacilityIds();
        $dateLimit = now()->subDays(15)->startOfDay();

        // Awaiting Pharmacy: pending prescriptions count
        $awaitingPharmacy = DB::table('prescriptions as p')
            ->join('clinical_consultations as cc', 'p.clinical_consultation_id', '=', 'cc.id')
            ->join('encounters as e', 'cc.encounter_id', '=', 'e.id')
            ->when($facilityIds !== null, fn($q) => $q->whereIn('e.facility_id', $facilityIds))
            ->where('e.visit_date', '>=', $dateLimit)
            ->where('p.status', 'Pending')
            ->count();

        // Awaiting Lab: pending/authorized lab order items count
        $awaitingLab = DB::table('service_order_items as soi')
            ->join('service_orders as so', 'soi.service_order_id', '=', 'so.id')
            ->join('encounters as e', 'so.encounter_id', '=', 'e.id')
            ->when($facilityIds !== null, fn($q) => $q->whereIn('e.facility_id', $facilityIds))
            ->where('e.visit_date', '>=', $dateLimit)
            ->whereIn('soi.status', ['pending', 'authorized'])
            ->count();

        $inConsultation = DB::table('encounters')
            ->when($facilityIds !== null, fn($q) => $q->whereIn('facility_id', $facilityIds))
            ->where('visit_date', '>=', $dateLimit)
            ->where('status', 'In Consultation')
            ->count();

        $triaged = DB::table('encounters')
            ->when($facilityIds !== null, fn($q) => $q->whereIn('facility_id', $facilityIds))
            ->where('visit_date', '>=', $dateLimit)
            ->where('status', 'Triaged')
            ->count();

        $registered = DB::table('encounters')
            ->when($facilityIds !== null, fn($q) => $q->whereIn('facility_id', $facilityIds))
            ->where('visit_date', '>=', $dateLimit)
            ->where('status', 'Registered')
            ->count();

        // Get base facility stats
        $baseFacilityStats = DB::table('encounters as e')
            ->join('facilities as f', 'e.facility_id', '=', 'f.id')
            ->select('e.facility_id', 'f.name as facility_name',
                DB::raw("SUM(CASE WHEN e.status = 'Registered' THEN 1 ELSE 0 END) as registered"),
                DB::raw("SUM(CASE WHEN e.status = 'Triaged' THEN 1 ELSE 0 END) as triaged"),
                DB::raw("SUM(CASE WHEN e.status = 'In Consultation' THEN 1 ELSE 0 END) as in_consultation"))
            ->when($facilityIds !== null, fn($q) => $q->whereIn('e.facility_id', $facilityIds))
            ->where('e.visit_date', '>=', $dateLimit)
            ->whereIn('e.status', ['Registered', 'Triaged', 'In Consultation'])
            ->groupBy('e.facility_id', 'f.name')
            ->get()
            ->keyBy('facility_id');

        // Get awaiting pharmacy per facility
        $pharmacyByFacility = DB::table('prescriptions as p')
            ->join('clinical_consultations as cc', 'p.clinical_consultation_id', '=', 'cc.id')
            ->join('encounters as e', 'cc.encounter_id', '=', 'e.id')
            ->select('e.facility_id', DB::raw('COUNT(*) as awaiting_pharmacy'))
            ->when($facilityIds !== null, fn($q) => $q->whereIn('e.facility_id', $facilityIds))
            ->where('e.visit_date', '>=', $dateLimit)
            ->where('p.status', 'Pending')
            ->groupBy('e.facility_id')
            ->get()
            ->keyBy('facility_id');

        // Get awaiting lab per facility
        $labByFacility = DB::table('service_order_items as soi')
            ->join('service_orders as so', 'soi.service_order_id', '=', 'so.id')
            ->join('encounters as e', 'so.encounter_id', '=', 'e.id')
            ->select('e.facility_id', DB::raw('COUNT(*) as awaiting_lab'))
            ->when($facilityIds !== null, fn($q) => $q->whereIn('e.facility_id', $facilityIds))
            ->where('e.visit_date', '>=', $dateLimit)
            ->whereIn('soi.status', ['pending', 'authorized'])
            ->groupBy('e.facility_id')
            ->get()
            ->keyBy('facility_id');

        // Merge all facility stats
        $waitingByFacility = $baseFacilityStats->map(function($facility) use ($pharmacyByFacility, $labByFacility) {
            $facility->awaiting_pharmacy = $pharmacyByFacility->get($facility->facility_id)->awaiting_pharmacy ?? 0;
            $facility->awaiting_lab = $labByFacility->get($facility->facility_id)->awaiting_lab ?? 0;
            return $facility;
        })->values()->sortByDesc('registered')->values();

        $waitingQueue = [
            'awaiting_pharmacy' => $awaitingPharmacy,
            'awaiting_lab'      => $awaitingLab,
            'in_consultation'   => $inConsultation,
            'triaged'           => $triaged,
            'registered'        => $registered,
            'total_waiting'     => $awaitingPharmacy + $awaitingLab + $inConsultation + $triaged + $registered,
            'by_facility'       => $waitingByFacility,
        ];

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $waitingQueue
            ]);
        }

        return view('admin.crm.facility-activity', compact('waitingQueue'));
    }

    /**
     * Get active staff (active in the last 30 minutes)
     */
    public function activeStaff(Request $request)
    {
        $activeSessionUserIds = DB::table('sessions')
            ->whereNotNull('user_id')
            ->where('last_activity', '>=', now()->subMinutes(30)->timestamp)
            ->pluck('user_id')
            ->unique();

        $facilityIds = $this->getCustomerCareFacilityIds();

        $activeStaffList = [];

        // Fetch from staff table (assuming global if no facility_id exists)
        if ($facilityIds === null) {
            $staffMembers = Staff::whereIn('id', $activeSessionUserIds)->get();
            foreach ($staffMembers as $staff) {
                $activeStaffList[] = [
                    'id' => $staff->id,
                    'name' => $staff->fullname,
                    'role' => 'Staff', // Default fallback
                    'phone' => $staff->phone,
                    'status' => 'Online',
                ];
            }
        }

        // Fetch from users table
        $usersQuery = \App\Models\User::whereIn('id', $activeSessionUserIds)
            ->with(['role', 'staffPosition']);
            
        if ($facilityIds !== null) {
            $usersQuery->whereIn('facility_id', $facilityIds);
        }
        
        $users = $usersQuery->get();
            
        foreach ($users as $user) {
            $roleName = 'User';
            if ($user->role) {
                $roleName = $user->role->name;
            } elseif ($user->staffPosition) {
                $roleName = $user->staffPosition->name;
            }
            
            $activeStaffList[] = [
                'id' => $user->id,
                'name' => $user->name,
                'role' => $roleName,
                'phone' => $user->phone,
                'status' => 'Online',
            ];
        }

        // Ensure unique by ID (in case someone is in both or we had overlap somehow)
        $activeStaffList = collect($activeStaffList)->unique('id')->values()->all();

        return response()->json([
            'success' => true,
            'data' => $activeStaffList
        ]);
    }

    /**
     * Get EHR Activity
     */
    public function ehrActivity(Request $request)
    {
        $ccFacilityIds = $this->getCustomerCareFacilityIds();
        $facilityIds = $ccFacilityIds !== null ? $ccFacilityIds : ($request->get('facility_id') ? [$request->get('facility_id')] : null);
        $dateLimit = now()->subDays(15)->startOfDay();
        
        $activities = collect();

        // 1. Consultations
        $consultationsQuery = \App\Models\ClinicalConsultation::with(['doctor', 'encounter.patient'])
            ->where('created_at', '>=', $dateLimit)
            ->latest('created_at')
            ->take(20);
        if ($facilityIds !== null) {
            $consultationsQuery->whereHas('encounter', function($q) use ($facilityIds) {
                $q->whereIn('facility_id', $facilityIds);
            });
        }
        foreach ($consultationsQuery->get() as $c) {
            $activities->push([
                'type' => 'Consultation',
                'staff' => $c->doctor ? $c->doctor->name : 'Unknown',
                'patient' => ($c->encounter && $c->encounter->patient) ? ($c->encounter->patient->full_info['fullname'] ?? $c->encounter->patient->enrollee_number) : 'Unknown',
                'timestamp' => $c->created_at,
                'time_formatted' => $c->created_at->diffForHumans()
            ]);
        }

        // 2. Vitals
        $vitalsQuery = \App\Models\VitalSign::with(['takenBy', 'encounter.patient'])
            ->where('created_at', '>=', $dateLimit)
            ->latest('created_at')
            ->take(20);
        if ($facilityIds !== null) {
            $vitalsQuery->whereHas('encounter', function($q) use ($facilityIds) {
                $q->whereIn('facility_id', $facilityIds);
            });
        }
        foreach ($vitalsQuery->get() as $v) {
            $activities->push([
                'type' => 'Vitals',
                'staff' => $v->takenBy ? $v->takenBy->name : 'Unknown',
                'patient' => ($v->encounter && $v->encounter->patient) ? ($v->encounter->patient->full_info['fullname'] ?? $v->encounter->patient->enrollee_number) : 'Unknown',
                'timestamp' => $v->created_at,
                'time_formatted' => $v->created_at->diffForHumans()
            ]);
        }

        // 3. Lab
        $labQuery = \App\Models\ServiceOrder::with(['orderedBy', 'patient'])
            ->where('created_at', '>=', $dateLimit)
            ->latest('created_at')
            ->take(20);
        if ($facilityIds !== null) {
            $labQuery->whereIn('facility_id', $facilityIds);
        }
        foreach ($labQuery->get() as $l) {
            $activities->push([
                'type' => 'Lab',
                'staff' => $l->orderedBy ? $l->orderedBy->name : 'Unknown',
                'patient' => $l->patient ? ($l->patient->full_info['fullname'] ?? $l->patient->enrollee_number) : 'Unknown',
                'timestamp' => $l->created_at,
                'time_formatted' => $l->created_at->diffForHumans()
            ]);
        }

        // 4. Pharmacy (Prescriptions)
        $rxQuery = \App\Models\Prescription::with(['consultation.doctor', 'consultation.encounter.patient'])
            ->latest('created_at')
            ->take(20);
        if ($facilityIds !== null) {
            $rxQuery->whereHas('consultation.encounter', function($q) use ($facilityIds) {
                $q->whereIn('facility_id', $facilityIds);
            });
        }
        foreach ($rxQuery->get() as $r) {
            $doctor = $r->consultation && $r->consultation->doctor ? $r->consultation->doctor->name : 'Unknown';
            $patient = ($r->consultation && $r->consultation->encounter && $r->consultation->encounter->patient) 
                ? ($r->consultation->encounter->patient->full_info['fullname'] ?? $r->consultation->encounter->patient->enrollee_number) 
                : 'Unknown';
            
            $activities->push([
                'type' => 'Pharmacy',
                'staff' => $doctor,
                'patient' => $patient,
                'timestamp' => $r->created_at,
                'time_formatted' => $r->created_at->diffForHumans()
            ]);
        }

        $recentActivities = $activities->sortByDesc('timestamp')->take(20)->values()->all();

        return response()->json([
            'success' => true,
            'data' => $recentActivities
        ]);
    }

    /**
     * Get Customer Care facility IDs
     */
    private function getCustomerCareFacilityIds()
    {
        $user = auth('staff')->user() ?? auth()->user();
        if (!$user) return [];
        
        $isCustomerCare = (method_exists($user, 'hasRole') && $user->hasRole('Customer Care')) ||
                          (isset($user->role) && $user->role->name === 'Customer Care');
                          
        if (!$isCustomerCare) return null; // null means not customer care, don't restrict
        
        $facilityIds = [];
        if (method_exists($user, 'facilities') && $user->facilities()->count() > 0) {
            $facilityIds = $user->facilities->pluck('id')->toArray();
        } elseif (isset($user->facility_id) && $user->facility_id) {
            $facilityIds = [$user->facility_id];
        }
        
        // Return [-1] if customer care but no facility assigned (so they see nothing instead of everything)
        return empty($facilityIds) ? [-1] : $facilityIds;
    }
}
