<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReinstatementRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'user_id',
        'reason',
        'status',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // التحقق من أن الطلب ما زال pending
    public function isPending()
    {
        return $this->status === 'pending';
    }

    // الموافقة على الطلب
    public function approve($reviewerId)
    {
        $this->update([
            'status' => 'approved',
            'reviewed_by' => $reviewerId,
            'reviewed_at' => now(),
        ]);

        // نعيد الحجز إلى pending مع وقت جديد
        $this->booking->update([
            'status' => 'pending',
            'expires_at' => now()->addMinutes(5),
        ]);
    }

    // رفض الطلب
    public function reject($reviewerId)
    {
        $this->update([
            'status' => 'rejected',
            'reviewed_by' => $reviewerId,
            'reviewed_at' => now(),
        ]);
    }
}
