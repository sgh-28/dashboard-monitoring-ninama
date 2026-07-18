<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarketingOfferHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'marketing_offer_id',
        'changed_by',
        'status',
        'follow_up_date',
        'notes',
    ];

    protected $casts = [
        'follow_up_date' => 'date',
    ];

    public function offer()
    {
        return $this->belongsTo(MarketingOffer::class, 'marketing_offer_id');
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    public function getStatusLabelAttribute(): string
    {
        return MarketingOffer::statusLabels()[$this->status] ?? $this->status;
    }
}
