<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles , HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'phone',
        'otp',
        'expires_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'expires_at' => 'datetime',
        ];
    }



    // العلاقات
    public function hotels()
    {
        return $this->hasMany(Hotel::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function reinstatementRequests()
    {
        return $this->hasMany(ReinstatementRequest::class);
    }

    public function reviewedReinstatementRequests()
    {
        return $this->hasMany(ReinstatementRequest::class, 'reviewed_by');
    }
}
