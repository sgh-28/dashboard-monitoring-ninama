<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'project_task_id',
        'title',
        'message',
        'channel',
        'status',
        'response_log',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'sent_at' => 'datetime',
    ];

    /**
     * Get the user that owns the notification.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the project task associated with the notification.
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(ProjectTask::class, 'project_task_id');
    }

    /**
     * Scope: Get notifications by channel
     */
    public function scopeByChannel($query, $channel)
    {
        return $query->where('channel', $channel);
    }

    /**
     * Scope: Get notifications by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Get channel label
     */
    public function getChannelLabelAttribute(): string
    {
        return match($this->channel) {
            'whatsapp' => 'WhatsApp',
            'email' => 'Email',
            'calendar' => 'Google Calendar',
            default => ucfirst($this->channel),
        };
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'sent' => 'bg-green-500/20 text-green-400',
            'failed' => 'bg-red-500/20 text-red-400',
            'pending' => 'bg-yellow-500/20 text-yellow-400',
            default => 'bg-gray-500/20 text-gray-400',
        };
    }
}