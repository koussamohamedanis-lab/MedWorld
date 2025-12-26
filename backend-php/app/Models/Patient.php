<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'userId',
        'emergencyContact',
        'bloodType',
        'weight',
        'medicalHistory',
        'allergies',
    ];

    protected $casts = [
        'weight' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'patientId');
    }

    public function consultations()
    {
        return $this->hasMany(Consultation::class, 'patientId');
    }
}
