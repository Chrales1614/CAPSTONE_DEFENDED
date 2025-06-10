<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'user_id',
        'appointment_date',
        'appointment_time',
        'type', // 'scheduled' or 'walk-in'
        'status', // 'pending', 'confirmed', 'completed', 'cancelled'
        'service_type', // 'checkup', 'vaccination', 'medical_mission'
        'notes',
    ];

    protected $casts = [
        'appointment_date' => 'date',
        'appointment_time' => 'datetime',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
} 