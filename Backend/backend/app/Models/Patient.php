<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'species',
        'breed',
        'age',
        'gender',
        'owner_name',
        'owner_contact',
        'owner_address',
        'medical_history',
        'last_visit',
    ];

    protected $casts = [
        'medical_history' => 'array',
        'last_visit' => 'datetime',
    ];

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function medicalRecords()
    {
        return $this->hasMany(MedicalRecord::class);
    }
} 