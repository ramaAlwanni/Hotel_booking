<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'hotel_id',
        'room_id',
        'check_in',
        'check_out',
        'total_price',
        'status',
        'expires_at',
    ];

    protected $casts = [
        'check_in' => 'date',
        'check_out' => 'date',
        'expires_at' => 'datetime',
        'total_price' => 'decimal:2',
    ];

    // العلاقات
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function reinstatementRequests()
    {
        return $this->hasMany(ReinstatementRequest::class);
    }

    // حساب عدد الليالي
    public function getNightsCountAttribute()
    {
        return $this->check_in->diffInDays($this->check_out);
    }

    // التحقق من صلاحية الحجز للدفع
    public function isPayable()
    {
        return $this->status === 'pending' && !$this->expires_at->isPast();
    }

    // التحقق من إمكانية الإلغاء
    public function isCancellable()
    {
        return $this->status === 'confirmed'
            && $this->check_in->diffInDays(now()) >= 1;
    }
}
