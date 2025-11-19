<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_type',
        'user_id',
        'user_email',
        'action',
        'module',
        'affected_id',
        'affected_name',
        'details',
        'ip_address',
        'user_agent',
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'details' => 'json',
    ];
    
    /**
     * Get the user that performed the action.
     */
    public function user(): MorphTo
    {
        return $this->morphTo('user');
    }
    
    /**
     * Static method to log an activity
     *
     * @param string $action The action performed (create, update, delete, assign)
     * @param string $module The module affected (role, permission, staff)
     * @param string|null $affectedId ID of the affected entity
     * @param string|null $affectedName Name of the affected entity
     * @param array|null $details Additional details as JSON
     * @return ActivityLog|null
     */
    public static function log(string $action, string $module, ?string $affectedId = null, ?string $affectedName = null, ?array $details = null): ?ActivityLog
    {
        // Get the authenticated user
        $user = auth()->user() ?? auth('staff')->user();
        
        if (!$user) {
            return null;
        }
        
        $request = request();
        
        return self::create([
            'user_type' => get_class($user),
            'user_id' => $user->id,
            'user_email' => $user->email,
            'action' => $action,
            'module' => $module,
            'affected_id' => $affectedId,
            'affected_name' => $affectedName,
            'details' => $details,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}
