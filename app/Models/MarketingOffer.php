<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MarketingOffer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_name',
        'company_address',
        'contact_person',
        'contact_phone',
        'contact_email',
        'category',
        'offer_description',
        'estimated_value',
        'offer_date',
        'follow_up_date',
        'meeting_date',
        'status',
        'reason',
        'notes',
        'employee_id',
        'project_id',
    ];

    protected $casts = [
        'offer_date' => 'date',
        'follow_up_date' => 'date',
        'meeting_date' => 'datetime',
        'estimated_value' => 'decimal:2',
    ];

    /**
     * Relasi ke User (Marketing/Employee)
     */
    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    /**
     * Relasi ke Project (jika deal)
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Scope untuk filter berdasarkan kategori
     */
    public function scopeCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope untuk filter berdasarkan status
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope untuk filter berdasarkan employee
     */
    public function scopeByEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    /**
     * Get status label (indonesia)
     */
    public function getStatusLabelAttribute()
    {
        $labels = [
            'penawaran' => 'Penawaran',
            'follow_up' => 'Follow Up',
            'meeting' => 'Meeting',
            'menunggu_keputusan' => 'Menunggu Keputusan',
            'negosiasi' => 'Negosiasi',
            'deal' => 'Deal/Closing',
            'pending' => 'Pending',
            'rejected' => 'Ditolak',
            'no_response' => 'No Response',
        ];

        return $labels[$this->status] ?? $this->status;
    }

    /**
     * Get status color for UI
     */
    public function getStatusColorAttribute()
    {
        $colors = [
            'penawaran' => 'blue',
            'follow_up' => 'yellow',
            'meeting' => 'purple',
            'menunggu_keputusan' => 'gray',
            'negosiasi' => 'orange',
            'deal' => 'green',
            'pending' => 'red',
            'rejected' => 'red',
            'no_response' => 'gray',
        ];

        return $colors[$this->status] ?? 'gray';
    }

    /**
     * Check if offer is successful
     */
    public function isSuccessful()
    {
        return $this->status === 'deal';
    }

    /**
     * Check if offer is rejected
     */
    public function isRejected()
    {
        return in_array($this->status, ['rejected', 'no_response']);
    }
}