<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'time_in',
        'time_out',
        'status', // 'present', 'late', 'absent'
        'biometric_id',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'time_in' => 'datetime',
        'time_out' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function calculateWorkHours()
    {
        if ($this->time_in && $this->time_out) {
            return $this->time_out->diffInHours($this->time_in);
        }
        return 0;
    }
} 