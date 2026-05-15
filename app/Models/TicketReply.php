<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketReply extends Model
{
    protected $fillable = [
        'ticket_id',
        'user_id',
        'message',
        'attachment_path',
        'attachment_name',
        'attachments',
        'reply_type',
        'is_internal',
        'read_by_assigned_at'
    ];

    protected $casts = [
        'is_internal' => 'boolean',
        'read_by_assigned_at' => 'datetime',
        'attachments' => 'array'
    ];

    // Auto-update ticket status when reply is created
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($reply) {
            // When a reply is created, update the ticket status to "in_progress"
            $ticket = $reply->ticket;
            if ($ticket && $ticket->status !== 'in_progress') {
                $ticket->status = 'in_progress';
                $ticket->save();
            }
        });
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user()
    {
        return $this->belongsTo(Staff::class);
    }

    public function getAttachmentUrl()
    {
        // Backward compatibility for single attachment
        if (!$this->attachment_path) {
            return null;
        }
        
        return asset('storage/' . $this->attachment_path);
    }

    public function hasAttachment()
    {
        // Check for multiple attachments first
        if (!empty($this->attachments)) {
            return true;
        }
        
        // Backward compatibility for single attachment
        return !empty($this->attachment_path) && file_exists(storage_path('app/public/' . $this->attachment_path));
    }

    public function getAttachmentSize()
    {
        // For multiple attachments, return total size
        if (!empty($this->attachments)) {
            $totalSize = 0;
            foreach ($this->attachments as $attachment) {
                if (isset($attachment['size'])) {
                    $totalSize += $attachment['size'];
                }
            }
            return $this->formatBytes($totalSize);
        }
        
        // Backward compatibility for single attachment
        if (!$this->hasAttachment()) {
            return null;
        }
        
        $size = filesize(storage_path('app/public/' . $this->attachment_path));
        return $this->formatBytes($size);
    }

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
        
        // Backward compatibility for single attachment
        if ($this->attachment_path) {
            $size = 0;
            $filePath = storage_path('app/public/' . $this->attachment_path);
            if (file_exists($filePath)) {
                $size = filesize($filePath);
            }
            
            return [[
                'path' => $this->attachment_path,
                'name' => $this->attachment_name,
                'size' => $size,
                'url' => $this->getAttachmentUrl()
            ]];
        }
        
        return [];
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
