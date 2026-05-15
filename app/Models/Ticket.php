<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Ticket extends Model
{
    protected $fillable = [
        'ticket_id',
        'boschma_no',
        'name',
        'phone',
        'email',
        'beneficiary_type',
        'facility_id',
        'ticket_category_id',
        'assigned_to',
        'created_by',
        'complaint',
        'description',
        'department',
        'sla_hours',
        'status',
        'priority',
        'resolved_at',
        'due_date',
        'attachments'
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'resolved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'sla_hours' => 'integer',
        'attachments' => 'array'
    ];

    // Auto-generate ticket_id when creating
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($ticket) {
            if (empty($ticket->ticket_id)) {
                $ticket->ticket_id = 'TKT-' . strtoupper(Str::random(8));
            }
            if (empty($ticket->due_date)) {
                $slaHours = (int) ($ticket->sla_hours ?? 24);
                $ticket->due_date = now()->addHours($slaHours);
            }
        });

        static::updating(function ($ticket) {
            if ($ticket->status === 'completed' && !$ticket->resolved_at) {
                $ticket->resolved_at = now();
            }
            if ($ticket->status !== 'completed' && $ticket->resolved_at) {
                $ticket->resolved_at = null;
            }
            
            // Update due_date if sla_hours has changed and ticket is not completed
            if ($ticket->isDirty('sla_hours') && $ticket->status !== 'completed') {
                $slaHours = (int) ($ticket->sla_hours ?? 24);
                // Calculate from original creation time, not current time
                $ticket->due_date = $ticket->created_at->addHours($slaHours);
            }
        });
    }

    public function category()
    {
        return $this->belongsTo(TicketCategory::class, 'ticket_category_id');
    }

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    public function assignedUser()
    {
        return $this->belongsTo(Staff::class, 'assigned_to');
    }

    public function createdBy()
    {
        return $this->belongsTo(Staff::class, 'created_by');
    }

    public function replies()
    {
        return $this->hasMany(TicketReply::class)->orderBy('created_at', 'asc');
    }

    public function publicReplies()
    {
        return $this->hasMany(TicketReply::class)->where('is_internal', false)->orderBy('created_at', 'asc');
    }

    // Permission methods
    public function canBeCompletedBy($staff)
    {
        // Only the ticket riser (created_by) can complete the ticket
        return $this->created_by === $staff->id;
    }

    public function canBeEditedBy($staff)
    {
        // Only the ticket riser (created_by) can edit the ticket
        return $this->created_by === $staff->id;
    }

    public function canBeRepliedBy($staff)
    {
        // Only the ticket riser (created_by) or assigned user can reply
        return $this->created_by === $staff->id || $this->assigned_to === $staff->id;
    }

    public function markAsCompleted()
    {
        $this->status = 'completed';
        $this->resolved_at = now();
        return $this->save();
    }

    public function canBeDeletedBy($staff)
    {
        // Only the ticket riser (created_by) can delete, and only if status is 'pending'
        return $this->created_by === $staff->id && $this->status === 'pending';
    }

    public function internalNotes()
    {
        return $this->hasMany(TicketReply::class)->where('is_internal', true)->orderBy('created_at', 'asc');
    }

    // Scopes
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopePriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())->where('status', '!=', 'completed');
    }

    // Methods
    public function isOverdue()
    {
        return $this->due_date->isPast() && $this->status !== 'completed';
    }

    public function getStatusColor()
    {
        return [
            'pending' => '#ffc107',
            'in_progress' => '#17a2b8', 
            'completed' => '#28a745'
        ][$this->status] ?? '#6c757d';
    }

    public function getPriorityColor()
    {
        return [
            'low' => '#28a745',
            'medium' => '#ffc107',
            'high' => '#fd7e14', 
            'urgent' => '#dc3545'
        ][$this->priority] ?? '#6c757d';
    }

    public function addReply($message, $userId, $isInternal = false, $attachments = [])
    {
        return $this->replies()->create([
            'message' => $message,
            'user_id' => $userId,
            'is_internal' => $isInternal,
            'attachments' => !empty($attachments) ? json_encode($attachments) : null,
        ]);
    }

    // Ticket attachment methods
    public function getAttachments()
    {
        // Return multiple attachments if available
        if (!empty($this->attachments)) {
            // Handle both array and JSON string cases
            if (is_string($this->attachments)) {
                $decoded = json_decode($this->attachments, true);
                if (is_array($decoded)) {
                    return $decoded;
                }
            } elseif (is_array($this->attachments)) {
                return $this->attachments;
            }
        }
        
        return [];
    }

    public function hasAttachment()
    {
        return !empty($this->attachments);
    }

    public function getAttachmentUrl()
    {
        // Return first attachment URL
        $attachments = $this->getAttachments();
        if (!empty($attachments) && isset($attachments[0]['path'])) {
            return asset('storage/' . $attachments[0]['path']);
        }
        
        return null;
    }

    public function getAttachmentSize()
    {
        // For multiple attachments, return total size
        $attachments = $this->getAttachments();
        $totalSize = 0;
        foreach ($attachments as $att) {
            $totalSize += isset($att['size']) ? (float)$att['size'] : 0;
        }
        return $totalSize;
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
