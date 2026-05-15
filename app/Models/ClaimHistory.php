<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Staff;

class ClaimHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'claim_id',
        'action',
        'description',
        'user_id',
        'old_status',
        'new_status',
        'ip_address',
        'user_agent'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the claim that owns the history record.
     */
    public function claim()
    {
        return $this->belongsTo(Claim::class);
    }

    /**
     * Get the user who performed the action.
     */
    public function user()
    {
        return $this->belongsTo(Staff::class, 'user_id');
    }

    /**
     * Get action label.
     */
    public function getActionLabelAttribute()
    {
        $actions = [
            'created' => 'Claim Created',
            'updated' => 'Claim Updated',
            'approved' => 'Claim Approved',
            'rejected' => 'Claim Rejected',
            'paid' => 'Claim Paid',
            'ro_reviewed' => 'RO Review',
            'e5_reviewed' => 'E5 Review',
            'document_added' => 'Document Added',
            'note_added' => 'Note Added',
        ];

        return $actions[$this->action] ?? ucfirst($this->action);
    }
}
